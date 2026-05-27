<?php

namespace Tests\Feature;

use App\Models\Country;
use App\Models\OtpOrder;
use App\Models\OtpService;
use App\Models\PaymentInvoice;
use App\Models\Provider;
use App\Models\ProviderSyncLog;
use App\Models\ProviderSyncScope;
use App\Models\User;
use App\Filament\Widgets\ActiveOtpOrdersWidget;
use App\Filament\Widgets\OperationalStatsWidget;
use App\Filament\Widgets\PendingPaymentInvoicesWidget;
use App\Services\Audit\AuditLogService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_regular_user_can_not_access_admin_panel(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/admin')
            ->assertForbidden();
    }

    public function test_super_admin_can_access_admin_panel(): void
    {
        $admin = User::factory()->superAdmin()->create();

        $this->actingAs($admin)
            ->get('/admin')
            ->assertOk();
    }

    public function test_admin_dashboard_renders_operational_widgets(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $user = User::factory()->create();
        $provider = Provider::create([
            'code' => 'smsbower',
            'name' => 'SMSBower',
            'is_active' => true,
            'last_balance' => '12.50',
            'balance_checked_at' => now(),
        ]);
        $service = OtpService::create(['provider_id' => $provider->id, 'provider_code' => 'go', 'name' => 'Google']);
        $country = Country::create(['provider_id' => $provider->id, 'provider_code' => '6', 'name' => 'Indonesia']);

        PaymentInvoice::create([
            'invoice_no' => 'TOPUP-DASHBOARD',
            'user_id' => $user->id,
            'provider' => 'dompetx',
            'external_id' => 'checkout-dashboard',
            'idempotency_key' => 'idem-dashboard',
            'amount' => '50000.00',
            'net_amount' => '50000.00',
            'status' => 'pending',
            'expired_at' => now()->addMinutes(10),
        ]);

        OtpOrder::create([
            'order_no' => 'OTP-DASHBOARD',
            'user_id' => $user->id,
            'provider_id' => $provider->id,
            'otp_service_id' => $service->id,
            'country_id' => $country->id,
            'provider_cost' => '50000.0000',
            'selling_price' => '50000.00',
            'margin_amount' => '0.00',
            'status' => 'waiting_sms',
            'expires_at' => now()->addMinutes(20),
        ]);

        $this->actingAs($admin)
            ->get('/admin')
            ->assertOk()
            ->assertSee('App\\Filament\\Widgets\\OperationalStatsWidget');

        Livewire::actingAs($admin)
            ->test(OperationalStatsWidget::class)
            ->assertSee('Operasional hari ini')
            ->assertSee('Saldo SMSBower');

        Livewire::actingAs($admin)
            ->test(PendingPaymentInvoicesWidget::class)
            ->assertSee('Top up pending')
            ->assertSee('TOPUP-DASHBOARD');

        Livewire::actingAs($admin)
            ->test(ActiveOtpOrdersWidget::class)
            ->assertSee('Order OTP aktif dan problem')
            ->assertSee('OTP-DASHBOARD');
    }

    public function test_admin_can_view_provider_sync_observability_pages(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $provider = Provider::create([
            'code' => 'smsbower',
            'name' => 'SMSBower',
            'is_active' => true,
        ]);

        ProviderSyncLog::create([
            'provider_id' => $provider->id,
            'created_by_user_id' => $admin->id,
            'sync_type' => 'scoped',
            'country_code' => '6',
            'service_code' => 'wa',
            'status' => 'success',
            'processed_items' => 10,
            'started_at' => now()->subSeconds(2),
            'finished_at' => now(),
            'duration_ms' => 2000,
            'meta' => ['prices' => 10],
        ]);

        ProviderSyncScope::create([
            'provider_id' => $provider->id,
            'scope_key' => 'country:6:service:wa',
            'country_code' => '6',
            'service_code' => 'wa',
            'status' => 'success',
            'last_success_at' => now(),
        ]);

        $this->actingAs($admin)
            ->get('/admin/provider-sync-logs')
            ->assertOk()
            ->assertSee('Sync Logs')
            ->assertSee('wa');

        $this->actingAs($admin)
            ->get('/admin/provider-sync-scopes')
            ->assertOk()
            ->assertSee('Sync Scopes')
            ->assertSee('country:6:service:wa');
    }

    public function test_suspend_action_can_be_audited(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $user = User::factory()->create();

        $user->forceFill(['status' => 'suspended'])->save();
        app(AuditLogService::class)->record('user.suspended', $admin, $user);

        $this->assertDatabaseHas('audit_logs', [
            'actor_user_id' => $admin->id,
            'action' => 'user.suspended',
            'target_type' => $user->getMorphClass(),
            'target_id' => $user->id,
        ]);
    }
}
