<?php

namespace Tests\Feature;

use App\Models\OperationalAlert;
use App\Models\PaymentInvoice;
use App\Models\PaymentWebhookEvent;
use App\Models\Provider;
use App\Models\User;
use App\Services\Operations\OperationalAlertService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class OperationalAlertTest extends TestCase
{
    use RefreshDatabase;

    public function test_operational_alerts_detect_low_provider_balance_and_are_deduplicated(): void
    {
        config()->set('operations.provider_low_balance_threshold', 5);
        Provider::create([
            'code' => 'smsbower',
            'name' => 'SMSBower',
            'is_active' => true,
            'last_balance' => '1.25',
            'balance_checked_at' => now(),
        ]);

        $result = app(OperationalAlertService::class)->check();
        app(OperationalAlertService::class)->check();

        $this->assertSame(1, $result['provider_low_balance']);
        $this->assertDatabaseCount('operational_alerts', 1);
        $this->assertDatabaseHas('operational_alerts', [
            'type' => 'provider_low_balance',
            'severity' => 'critical',
            'resolved_at' => null,
        ]);
    }

    public function test_operational_alerts_detect_overdue_pending_invoice(): void
    {
        config()->set('operations.pending_invoice_overdue_minutes', 30);
        $user = User::factory()->create();
        PaymentInvoice::create([
            'invoice_no' => 'TOPUP-OVERDUE',
            'user_id' => $user->id,
            'provider' => 'dompetx',
            'idempotency_key' => 'idem-overdue',
            'amount' => '10000.00',
            'net_amount' => '10000.00',
            'status' => 'pending',
            'expired_at' => now()->subHour(),
        ]);

        app(OperationalAlertService::class)->check();

        $this->assertDatabaseHas('operational_alerts', [
            'type' => 'pending_invoice_overdue',
            'dedupe_key' => 'pending_invoice_overdue',
        ]);
    }

    public function test_operational_alerts_detect_payment_webhook_error_spike(): void
    {
        config()->set('operations.webhook_error_window_minutes', 60);
        config()->set('operations.webhook_error_threshold', 2);

        PaymentWebhookEvent::create([
            'provider' => 'dompetx',
            'event_id' => 'evt-1',
            'payload' => [],
            'signature_valid' => false,
            'processed' => false,
            'error_message' => 'Invalid signature.',
        ]);
        PaymentWebhookEvent::create([
            'provider' => 'dompetx',
            'event_id' => 'evt-2',
            'payload' => [],
            'signature_valid' => true,
            'processed' => false,
            'error_message' => 'Invoice not found.',
        ]);

        app(OperationalAlertService::class)->check();

        $this->assertDatabaseHas('operational_alerts', [
            'type' => 'payment_webhook_errors',
            'severity' => 'critical',
        ]);
    }

    public function test_admin_can_view_and_resolve_operational_alert(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $alert = OperationalAlert::create([
            'type' => 'provider_low_balance',
            'severity' => 'critical',
            'dedupe_key' => 'provider_low_balance:1',
            'title' => 'Provider balance low',
            'message' => 'Provider balance is low.',
        ]);

        app(OperationalAlertService::class)->resolve($alert, $admin);

        $this->assertNotNull($alert->fresh()->resolved_at);
        $this->assertSame($admin->id, $alert->fresh()->resolved_by_user_id);

        $this->actingAs($admin)
            ->get('/admin/operational-alerts')
            ->assertOk()
            ->assertSee('Provider balance low');
    }

    public function test_check_alerts_command_runs(): void
    {
        $exitCode = Artisan::call('ops:check-alerts');

        $this->assertSame(0, $exitCode);
        $this->assertStringContainsString('Operational alerts checked', Artisan::output());
    }
}
