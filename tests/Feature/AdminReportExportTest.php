<?php

namespace Tests\Feature;

use App\Models\Country;
use App\Models\OtpOrder;
use App\Models\OtpService;
use App\Models\PaymentInvoice;
use App\Models\Provider;
use App\Models\User;
use App\Services\Wallet\WalletService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminReportExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_regular_user_cannot_access_admin_reports(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('admin.reports.index'))
            ->assertForbidden();
    }

    public function test_admin_can_view_reports_page(): void
    {
        $admin = User::factory()->superAdmin()->create();

        $this->actingAs($admin)
            ->get(route('admin.reports.index'))
            ->assertOk()
            ->assertSee('Reports & Export', false)
            ->assertSee('Payment invoices')
            ->assertSee('Profit report');
    }

    public function test_admin_can_export_payment_invoices_csv(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $user = User::factory()->create(['email' => 'buyer@example.test']);
        PaymentInvoice::create([
            'invoice_no' => 'TOPUP-EXPORT',
            'user_id' => $user->id,
            'provider' => 'dompetx',
            'external_id' => 'checkout-export',
            'idempotency_key' => 'idem-export',
            'amount' => '10000.00',
            'fee' => '500.00',
            'net_amount' => '9500.00',
            'status' => 'paid',
            'payment_method' => 'qr_dynamic',
            'paid_at' => now(),
        ]);

        $response = $this->actingAs($admin)->get(route('admin.reports.payment-invoices'));

        $response->assertOk();
        $this->assertStringContainsString('text/csv', $response->headers->get('content-type'));

        $csv = $response->streamedContent();
        $this->assertStringContainsString('invoice_no,user_email,provider', $csv);
        $this->assertStringContainsString('TOPUP-EXPORT,buyer@example.test,dompetx', $csv);
    }

    public function test_admin_can_export_wallet_transactions_csv(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $user = User::factory()->create(['email' => 'wallet@example.test']);

        app(WalletService::class)->credit($user, '25000.00', 'topup');

        $csv = $this->actingAs($admin)
            ->get(route('admin.reports.wallet-transactions'))
            ->assertOk()
            ->streamedContent();

        $this->assertStringContainsString('created_at,user_email,type,direction,amount', $csv);
        $this->assertStringContainsString('wallet@example.test,topup,credit,25000.00', $csv);
    }

    public function test_admin_can_export_otp_orders_and_profit_csv(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $user = User::factory()->create(['email' => 'otp@example.test']);
        $order = $this->orderFor($user);

        $ordersCsv = $this->actingAs($admin)
            ->get(route('admin.reports.otp-orders'))
            ->assertOk()
            ->streamedContent();

        $this->assertStringContainsString('order_no,user_email,provider', $ordersCsv);
        $this->assertStringContainsString($order->order_no.',otp@example.test,smsbower', $ordersCsv);

        $profitCsv = $this->actingAs($admin)
            ->get(route('admin.reports.profit'))
            ->assertOk()
            ->streamedContent();

        $this->assertStringContainsString('date,order_no,service,country,status,selling_price,provider_cost,margin_amount', $profitCsv);
        $this->assertStringContainsString($order->order_no.',WhatsApp,Indonesia,success,2000.00,0.1000,400.00', $profitCsv);
    }

    private function orderFor(User $user): OtpOrder
    {
        $provider = Provider::create(['code' => 'smsbower', 'name' => 'SMSBower', 'is_active' => true]);
        $service = OtpService::create(['provider_id' => $provider->id, 'provider_code' => 'wa', 'name' => 'WhatsApp']);
        $country = Country::create(['provider_id' => $provider->id, 'provider_code' => '6', 'name' => 'Indonesia']);

        return OtpOrder::create([
            'order_no' => 'OTP-EXPORT',
            'user_id' => $user->id,
            'provider_id' => $provider->id,
            'otp_service_id' => $service->id,
            'country_id' => $country->id,
            'provider_activation_id' => 'activation-export',
            'provider_cost' => '0.1000',
            'selling_price' => '2000.00',
            'margin_amount' => '400.00',
            'status' => 'success',
            'completed_at' => now(),
        ]);
    }
}
