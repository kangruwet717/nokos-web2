<?php

namespace Tests\Feature;

use App\Models\Country;
use App\Models\OtpOrder;
use App\Models\OtpService;
use App\Models\Provider;
use App\Models\ServicePrice;
use App\Models\User;
use App\Services\Orders\OtpOrderService;
use App\Services\Providers\SmsProviderInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class OtpOrderFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('services.smsbower.refresh_price_before_order', false);
    }

    public function test_user_can_create_otp_order_and_wallet_is_held(): void
    {
        $this->bindProvider(new FakeSmsProvider);
        [$user, $price] = $this->catalogFixture();

        $response = $this->actingAs($user)->post('/otp/orders', [
            'service_price_id' => $price->id,
        ]);

        $order = OtpOrder::firstOrFail();

        $response->assertRedirect(route('otp.orders.show', $order));
        $this->assertSame('waiting_sms', $order->status);
        $this->assertSame('activation-1', $order->provider_activation_id);
        $this->assertSame('50000.00', $user->fresh()->reserved_balance);
        $this->assertDatabaseHas('wallet_transactions', [
            'type' => 'order_hold',
            'direction' => 'hold',
            'reference_type' => $order->getMorphClass(),
            'reference_id' => $order->id,
        ]);
    }

    public function test_refresh_success_charges_wallet_once(): void
    {
        $provider = new FakeSmsProvider(['status' => 'success', 'code' => '123456']);
        $this->bindProvider($provider);
        [$user, $price] = $this->catalogFixture();
        $this->actingAs($user)->post('/otp/orders', ['service_price_id' => $price->id]);
        $order = OtpOrder::firstOrFail();

        $this->actingAs($user)->get(route('otp.orders.status', $order))->assertOk();
        $this->actingAs($user)->get(route('otp.orders.status', $order))->assertOk();

        $order->refresh();
        $this->assertSame('success', $order->status);
        $this->assertSame('123456', $order->sms_code);
        $this->assertSame('50000.00', $user->fresh()->balance);
        $this->assertSame('0.00', $user->fresh()->reserved_balance);
        $this->assertDatabaseCount('wallet_transactions', 2);
    }

    public function test_user_can_cancel_waiting_order_and_release_hold(): void
    {
        $this->bindProvider(new FakeSmsProvider);
        [$user, $price] = $this->catalogFixture();
        $this->actingAs($user)->post('/otp/orders', ['service_price_id' => $price->id]);
        $order = OtpOrder::firstOrFail();

        $this->actingAs($user)->post(route('otp.orders.cancel', $order))->assertSessionHas('status');

        $this->assertSame('cancelled', $order->fresh()->status);
        $this->assertSame('100000.00', $user->fresh()->balance);
        $this->assertSame('0.00', $user->fresh()->reserved_balance);
        $this->assertDatabaseHas('wallet_transactions', ['type' => 'refund', 'direction' => 'release']);
    }

    public function test_smsbower_webhook_settles_order_once(): void
    {
        $this->bindProvider(new FakeSmsProvider);
        [$user, $price] = $this->catalogFixture();
        $this->actingAs($user)->post('/otp/orders', ['service_price_id' => $price->id]);
        $order = OtpOrder::firstOrFail();

        $payload = [
            'activationId' => $order->provider_activation_id,
            'service' => 'go',
            'text' => 'Your code is 777888',
            'code' => '777888',
            'country' => 6,
            'receivedAt' => now()->toDateTimeString(),
        ];

        $this->postJson('/webhooks/smsbower', $payload)->assertOk();
        $this->postJson('/webhooks/smsbower', $payload)->assertOk();

        $this->assertSame('success', $order->fresh()->status);
        $this->assertSame('777888', $order->fresh()->sms_code);
        $this->assertSame('50000.00', $user->fresh()->balance);
        $this->assertSame('0.00', $user->fresh()->reserved_balance);
        $this->assertDatabaseCount('provider_webhook_events', 1);
        $this->assertDatabaseCount('wallet_transactions', 2);
    }

    public function test_manual_refund_credits_successful_order_once(): void
    {
        $this->bindProvider(new FakeSmsProvider(['status' => 'success', 'code' => '123456']));
        [$user, $price] = $this->catalogFixture();
        $admin = User::factory()->superAdmin()->create();
        $this->actingAs($user)->post('/otp/orders', ['service_price_id' => $price->id]);
        $order = OtpOrder::firstOrFail();
        app(OtpOrderService::class)->refreshStatus($order);

        app(OtpOrderService::class)->manualRefund($order->fresh(), $admin, 'Support approved');

        $this->assertSame('refunded', $order->fresh()->status);
        $this->assertSame('100000.00', $user->fresh()->balance);
        $this->assertDatabaseHas('wallet_transactions', [
            'type' => 'refund',
            'direction' => 'credit',
            'created_by_admin_id' => $admin->id,
        ]);
        $this->assertDatabaseHas('audit_logs', ['action' => 'otp.order_refunded']);
    }

    public function test_refresh_waiting_command_expires_old_orders(): void
    {
        $this->bindProvider(new FakeSmsProvider);
        [$user, $price] = $this->catalogFixture();
        $this->actingAs($user)->post('/otp/orders', ['service_price_id' => $price->id]);
        $order = OtpOrder::firstOrFail();
        $order->forceFill(['expires_at' => now()->subMinute()])->save();

        Artisan::call('otp:refresh-waiting', ['--limit' => 10]);

        $this->assertSame('expired', $order->fresh()->status);
        $this->assertSame('100000.00', $user->fresh()->balance);
        $this->assertSame('0.00', $user->fresh()->reserved_balance);
    }

    public function test_user_can_not_exceed_active_order_limit(): void
    {
        config()->set('cache.default', 'array');
        $this->bindProvider(new FakeSmsProvider);
        [$user, $price] = $this->catalogFixture();
        putenv('OTP_MAX_ACTIVE_ORDERS_PER_USER=1');
        $_ENV['OTP_MAX_ACTIVE_ORDERS_PER_USER'] = 1;

        $this->actingAs($user)->post('/otp/orders', ['service_price_id' => $price->id]);

        $this->actingAs($user)
            ->post('/otp/orders', ['service_price_id' => $price->id])
            ->assertSessionHasErrors('service_price_id');

        putenv('OTP_MAX_ACTIVE_ORDERS_PER_USER=3');
        $_ENV['OTP_MAX_ACTIVE_ORDERS_PER_USER'] = 3;
    }

    public function test_order_creation_rejects_stale_price_change_before_holding_wallet(): void
    {
        config()->set('services.smsbower.refresh_price_before_order', true);
        config()->set('services.smsbower.usd_to_idr_rate', 16000);
        config()->set('services.smsbower.minimum_profit_idr', 0);
        $this->bindProvider(new FakeSmsProvider(prices: ['6' => ['go' => ['cost' => '3.7500', 'count' => 10]]]));
        [$user, $price] = $this->catalogFixture();

        $this->actingAs($user)
            ->post('/otp/orders', ['service_price_id' => $price->id])
            ->assertSessionHasErrors('service_price_id');

        $this->assertSame('100000.00', $user->fresh()->balance);
        $this->assertSame('0.00', $user->fresh()->reserved_balance);
        $this->assertSame('60000.00', $price->fresh()->selling_price);
        $this->assertDatabaseCount('otp_orders', 0);
        $this->assertDatabaseCount('wallet_transactions', 0);
    }

    public function test_order_creation_rejects_empty_provider_stock_before_holding_wallet(): void
    {
        config()->set('services.smsbower.refresh_price_before_order', true);
        $this->bindProvider(new FakeSmsProvider(prices: ['6' => ['go' => ['cost' => '3.1250', 'count' => 0]]]));
        [$user, $price] = $this->catalogFixture();

        $this->actingAs($user)
            ->post('/otp/orders', ['service_price_id' => $price->id])
            ->assertSessionHasErrors('service_price_id');

        $this->assertSame('100000.00', $user->fresh()->balance);
        $this->assertSame('0.00', $user->fresh()->reserved_balance);
        $this->assertFalse($price->fresh()->is_active);
        $this->assertDatabaseCount('otp_orders', 0);
        $this->assertDatabaseCount('wallet_transactions', 0);
    }

    public function test_order_creation_rejects_insufficient_balance_without_creating_order(): void
    {
        $this->bindProvider(new FakeSmsProvider);
        [$user, $price] = $this->catalogFixture();
        $user->forceFill(['balance' => '0.00'])->save();

        $this->actingAs($user)
            ->post('/otp/orders', ['service_price_id' => $price->id])
            ->assertSessionHasErrors('service_price_id');

        $this->assertSame('0.00', $user->fresh()->balance);
        $this->assertSame('0.00', $user->fresh()->reserved_balance);
        $this->assertDatabaseCount('otp_orders', 0);
        $this->assertDatabaseCount('otp_order_status_logs', 0);
        $this->assertDatabaseCount('wallet_transactions', 0);
    }

    private function catalogFixture(): array
    {
        $user = User::factory()->create(['balance' => '100000.00']);
        $provider = Provider::create(['code' => 'smsbower', 'name' => 'SMSBower', 'is_active' => true]);
        $service = OtpService::create(['provider_id' => $provider->id, 'provider_code' => 'go', 'name' => 'Google']);
        $country = Country::create(['provider_id' => $provider->id, 'provider_code' => '6', 'name' => 'Indonesia']);
        $price = ServicePrice::create([
            'provider_id' => $provider->id,
            'otp_service_id' => $service->id,
            'country_id' => $country->id,
            'provider_price' => '3.1250',
            'margin_type' => 'fixed',
            'margin_value' => '0.00',
            'selling_price' => '50000.00',
            'stock_count' => 10,
            'is_active' => true,
            'last_synced_at' => now(),
        ]);

        return [$user, $price];
    }

    private function bindProvider(SmsProviderInterface $provider): void
    {
        $this->app->instance(SmsProviderInterface::class, $provider);
    }
}

class FakeSmsProvider implements SmsProviderInterface
{
    public function __construct(
        private array $status = ['status' => 'waiting_sms'],
        private array $prices = ['6' => ['go' => ['cost' => '3.1250', 'count' => 10]]],
    ) {}

    public function getBalance(): string
    {
        return '10.00';
    }

    public function getServices(): array
    {
        return [];
    }

    public function getCountries(): array
    {
        return [];
    }

    public function getPrices(?string $service = null, ?string $country = null): array
    {
        return $this->prices;
    }

    public function requestNumber(string $service, string $country, ?string $maxPrice = null, ?string $providerId = null): array
    {
        return [
            'activationId' => 'activation-1',
            'phoneNumber' => '6281234567890',
            'activationCost' => '0.20',
            'countryCode' => $country,
        ];
    }

    public function getStatus(string $activationId): array
    {
        return $this->status;
    }

    public function cancel(string $activationId): bool
    {
        return true;
    }

    public function complete(string $activationId): bool
    {
        return true;
    }
}
