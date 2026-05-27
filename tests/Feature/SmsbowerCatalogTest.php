<?php

namespace Tests\Feature;

use App\Models\Country;
use App\Models\OtpService;
use App\Models\Provider;
use App\Models\ServicePrice;
use App\Models\User;
use App\Jobs\SyncSmsbowerCatalogJob;
use App\Services\Providers\SmsbowerCatalogSyncService;
use App\Services\Providers\SmsbowerClient;
use App\Services\Providers\SmsProviderInterface;
use App\Services\Providers\ProviderSyncTracker;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Tests\TestCase;

class SmsbowerCatalogTest extends TestCase
{
    use RefreshDatabase;

    public function test_smsbower_client_parses_balance_and_status(): void
    {
        config()->set('services.smsbower.api_key', 'test-key');
        config()->set('services.smsbower.base_url', 'https://smsbower.test/stubs/handler_api.php');

        Http::fake(function ($request) {
            $action = $request->data()['action'];

            return match ($action) {
                'getBalance' => Http::response('ACCESS_BALANCE:123.45'),
                'getStatus' => Http::response('STATUS_OK: 987654'),
                default => Http::response('BAD_ACTION'),
            };
        });

        $client = new SmsbowerClient;

        $this->assertSame('123.45', $client->getBalance());
        $this->assertSame(['status' => 'success', 'code' => '987654'], $client->getStatus('activation-1'));
    }

    public function test_smsbower_client_wraps_connection_timeout_with_friendly_message(): void
    {
        config()->set('services.smsbower.api_key', 'test-key');
        config()->set('services.smsbower.base_url', 'https://smsbower.test/stubs/handler_api.php');

        Http::fake(fn () => throw new ConnectionException('cURL error 28'));

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('SMSBower sedang tidak bisa dihubungi.');

        (new SmsbowerClient)->getBalance();
    }

    public function test_smsbower_sync_command_handles_provider_connection_failure(): void
    {
        $this->app->instance(SmsProviderInterface::class, new class implements SmsProviderInterface
        {
            public function getBalance(): string
            {
                throw new RuntimeException('SMSBower sedang tidak bisa dihubungi.');
            }

            public function getServices(): array
            {
                throw new RuntimeException('SMSBower sedang tidak bisa dihubungi.');
            }

            public function getCountries(): array
            {
                return [];
            }

            public function getPrices(?string $service = null, ?string $country = null): array
            {
                return [];
            }

            public function requestNumber(string $service, string $country, ?string $maxPrice = null, ?string $providerId = null): array
            {
                return [];
            }

            public function getStatus(string $activationId): array
            {
                return [];
            }

            public function cancel(string $activationId): bool
            {
                return false;
            }

            public function complete(string $activationId): bool
            {
                return false;
            }
        });

        $exitCode = Artisan::call('smsbower:sync-catalog');

        $this->assertSame(1, $exitCode);
        $this->assertStringContainsString('SMSBower sedang tidak bisa dihubungi.', Artisan::output());
    }

    public function test_catalog_sync_creates_services_countries_prices_and_balance(): void
    {
        config()->set('services.smsbower.default_margin_type', 'percent');
        config()->set('services.smsbower.default_margin_value', 30);
        config()->set('services.smsbower.usd_to_idr_rate', 16000);
        config()->set('services.smsbower.minimum_selling_price_idr', 1000);
        config()->set('services.smsbower.rounding_idr', 100);

        $this->app->instance(SmsProviderInterface::class, new class implements SmsProviderInterface
        {
            public function getBalance(): string
            {
                return '88.50';
            }

            public function getServices(): array
            {
                return [
                    ['code' => 'go', 'name' => 'Google'],
                    ['code' => 'wa', 'name' => 'WhatsApp'],
                ];
            }

            public function getCountries(): array
            {
                return [
                    '6' => ['id' => 6, 'eng' => 'Indonesia'],
                    '0' => ['id' => 0, 'eng' => 'Russia'],
                ];
            }

            public function getPrices(?string $service = null, ?string $country = null): array
            {
                return [
                    '6' => [
                        'go' => ['cost' => 0.20, 'count' => 12],
                        'wa' => ['cost' => 0.30, 'count' => 0],
                    ],
                ];
            }

            public function requestNumber(string $service, string $country, ?string $maxPrice = null, ?string $providerId = null): array
            {
                return [];
            }

            public function getStatus(string $activationId): array
            {
                return [];
            }

            public function cancel(string $activationId): bool
            {
                return true;
            }

            public function complete(string $activationId): bool
            {
                return true;
            }
        });

        $result = app(SmsbowerCatalogSyncService::class)->sync();

        $this->assertSame(2, $result['services']);
        $this->assertSame(2, $result['countries']);
        $this->assertSame(2, $result['prices']);
        $this->assertDatabaseHas('providers', ['code' => 'smsbower', 'last_balance' => '88.50']);
        $this->assertDatabaseHas('otp_services', ['provider_code' => 'go', 'name' => 'Google']);
        $this->assertDatabaseHas('countries', ['provider_code' => '6', 'name' => 'Indonesia', 'iso_code' => 'ID']);
        $this->assertDatabaseHas('service_prices', ['stock_count' => 12, 'is_active' => true, 'selling_price' => '4200.00']);
        $this->assertDatabaseHas('service_prices', ['stock_count' => 0, 'is_active' => false]);
    }

    public function test_catalog_sync_disables_stale_prices_not_returned_by_provider(): void
    {
        config()->set('services.smsbower.default_margin_type', 'percent');
        config()->set('services.smsbower.default_margin_value', 30);
        config()->set('services.smsbower.usd_to_idr_rate', 16000);

        $provider = Provider::create(['code' => 'smsbower', 'name' => 'SMSBower', 'is_active' => true]);
        $service = OtpService::create(['provider_id' => $provider->id, 'provider_code' => 'old', 'name' => 'Old']);
        $country = Country::create(['provider_id' => $provider->id, 'provider_code' => '99', 'name' => 'Old Country']);
        $stalePrice = ServicePrice::create([
            'provider_id' => $provider->id,
            'otp_service_id' => $service->id,
            'country_id' => $country->id,
            'provider_price' => '0.0100',
            'selling_price' => '0.02',
            'stock_count' => 99,
            'is_active' => true,
            'last_synced_at' => now()->subDay(),
        ]);

        $this->app->instance(SmsProviderInterface::class, new class implements SmsProviderInterface
        {
            public function getBalance(): string
            {
                return '88.50';
            }

            public function getServices(): array
            {
                return [['code' => 'go', 'name' => 'Google']];
            }

            public function getCountries(): array
            {
                return ['6' => ['id' => 6, 'eng' => 'Indonesia']];
            }

            public function getPrices(?string $service = null, ?string $country = null): array
            {
                return ['6' => ['go' => ['cost' => 0.20, 'count' => 12]]];
            }

            public function requestNumber(string $service, string $country, ?string $maxPrice = null, ?string $providerId = null): array
            {
                return [];
            }

            public function getStatus(string $activationId): array
            {
                return [];
            }

            public function cancel(string $activationId): bool
            {
                return true;
            }

            public function complete(string $activationId): bool
            {
                return true;
            }
        });

        app(SmsbowerCatalogSyncService::class)->sync();

        $this->assertFalse($stalePrice->fresh()->is_active);
        $this->assertSame(0, $stalePrice->fresh()->stock_count);
    }

    public function test_catalog_sync_creates_multiple_price_variants_from_prices_v3(): void
    {
        config()->set('services.smsbower.default_margin_type', 'percent');
        config()->set('services.smsbower.default_margin_value', 30);
        config()->set('services.smsbower.usd_to_idr_rate', 16000);
        config()->set('services.smsbower.minimum_selling_price_idr', 1000);
        config()->set('services.smsbower.rounding_idr', 100);

        $this->app->instance(SmsProviderInterface::class, new class implements SmsProviderInterface
        {
            public function getBalance(): string
            {
                return '88.50';
            }

            public function getServices(): array
            {
                return [['code' => 'go', 'name' => 'Google']];
            }

            public function getCountries(): array
            {
                return ['6' => ['id' => 6, 'eng' => 'Indonesia']];
            }

            public function getPrices(?string $service = null, ?string $country = null): array
            {
                return [
                    '6' => [
                        'go' => [
                            '3170' => ['price' => 0.20, 'count' => 12, 'provider_id' => 3170],
                            '4120' => ['price' => 0.30, 'count' => 7, 'provider_id' => 4120],
                        ],
                    ],
                ];
            }

            public function requestNumber(string $service, string $country, ?string $maxPrice = null, ?string $providerId = null): array
            {
                return [];
            }

            public function getStatus(string $activationId): array
            {
                return [];
            }

            public function cancel(string $activationId): bool
            {
                return true;
            }

            public function complete(string $activationId): bool
            {
                return true;
            }
        });

        $result = app(SmsbowerCatalogSyncService::class)->sync();

        $this->assertSame(2, $result['prices']);
        $this->assertDatabaseHas('service_prices', [
            'provider_price_key' => 'provider:3170',
            'provider_price' => '0.2000',
            'stock_count' => 12,
            'selling_price' => '4200.00',
        ]);
        $this->assertDatabaseHas('service_prices', [
            'provider_price_key' => 'provider:4120',
            'provider_price' => '0.3000',
            'stock_count' => 7,
            'selling_price' => '6300.00',
        ]);
    }

    public function test_user_can_view_catalog_page(): void
    {
        $user = User::factory()->create();
        $provider = Provider::create([
            'code' => 'smsbower',
            'name' => 'SMSBower',
            'base_url' => 'https://smsbower.page/stubs/handler_api.php',
            'is_active' => true,
        ]);

        $service = OtpService::create([
            'provider_id' => $provider->id,
            'provider_code' => 'go',
            'name' => 'Google',
            'icon_url' => 'https://example.test/google-custom.png',
            'is_active' => true,
        ]);

        $country = Country::create([
            'provider_id' => $provider->id,
            'provider_code' => '6',
            'name' => 'Indonesia',
            'is_active' => true,
        ]);

        ServicePrice::create([
            'provider_id' => $provider->id,
            'otp_service_id' => $service->id,
            'country_id' => $country->id,
            'provider_price' => '0.2000',
            'selling_price' => '0.26',
            'stock_count' => 12,
            'is_active' => true,
            'last_synced_at' => now(),
        ]);

        $this->actingAs($user)
            ->get('/otp')
            ->assertOk()
            ->assertSee('Indonesia')
            ->assertSee('https://flagcdn.com/w40/id.png', false);

        $this->actingAs($user)
            ->get('/otp?country_id='.$country->id)
            ->assertOk()
            ->assertSee('Google')
            ->assertSee('Indonesia')
            ->assertSee('https://example.test/google-custom.png', false);
    }

    public function test_catalog_prices_are_cheapest_first_and_can_be_sorted_by_stock(): void
    {
        $user = User::factory()->create();
        $provider = Provider::create([
            'code' => 'smsbower',
            'name' => 'SMSBower',
            'base_url' => 'https://smsbower.app/stubs/handler_api.php',
            'is_active' => true,
        ]);
        $service = OtpService::create([
            'provider_id' => $provider->id,
            'provider_code' => 'go',
            'name' => 'Google',
            'is_active' => true,
        ]);
        $country = Country::create([
            'provider_id' => $provider->id,
            'provider_code' => '6',
            'name' => 'Indonesia',
            'is_active' => true,
        ]);

        foreach ([
            ['key' => 'provider:expensive', 'price' => '6300.00', 'stock' => 99],
            ['key' => 'provider:cheap', 'price' => '600.00', 'stock' => 1],
            ['key' => 'provider:middle', 'price' => '800.00', 'stock' => 20],
        ] as $row) {
            ServicePrice::create([
                'provider_id' => $provider->id,
                'otp_service_id' => $service->id,
                'country_id' => $country->id,
                'provider_price_key' => $row['key'],
                'provider_price' => '0.2000',
                'selling_price' => $row['price'],
                'stock_count' => $row['stock'],
                'is_active' => true,
                'last_synced_at' => now(),
            ]);
        }

        $this->actingAs($user)
            ->get('/otp?country_id='.$country->id.'&service_id='.$service->id)
            ->assertOk()
            ->assertSee('Termurah')
            ->assertSeeInOrder(['Rp600', 'Rp800', 'Rp6.300']);

        $this->actingAs($user)
            ->get('/otp?country_id='.$country->id.'&service_id='.$service->id.'&sort=stock')
            ->assertOk()
            ->assertSeeInOrder(['Rp6.300', 'Rp800', 'Rp600']);
    }

    public function test_country_service_list_is_not_limited_to_first_120_services(): void
    {
        $user = User::factory()->create();
        $provider = Provider::create([
            'code' => 'smsbower',
            'name' => 'SMSBower',
            'base_url' => 'https://smsbower.app/stubs/handler_api.php',
            'is_active' => true,
        ]);
        $country = Country::create([
            'provider_id' => $provider->id,
            'provider_code' => '6',
            'name' => 'Indonesia',
            'is_active' => true,
        ]);

        for ($i = 1; $i <= 130; $i++) {
            $serviceName = 'Service '.str_pad((string) $i, 3, '0', STR_PAD_LEFT);
            $service = OtpService::create([
                'provider_id' => $provider->id,
                'provider_code' => 'svc'.$i,
                'name' => $serviceName,
                'is_active' => true,
            ]);

            ServicePrice::create([
                'provider_id' => $provider->id,
                'otp_service_id' => $service->id,
                'country_id' => $country->id,
                'provider_price' => '0.2000',
                'selling_price' => '1000.00',
                'stock_count' => 12,
                'is_active' => true,
                'last_synced_at' => now(),
            ]);
        }

        $this->actingAs($user)
            ->get('/otp?country_id='.$country->id)
            ->assertOk()
            ->assertSee('130 layanan tersedia untuk Indonesia')
            ->assertSee('Service 001')
            ->assertSee('Service 130');
    }

    public function test_indonesia_is_shown_first_in_country_list(): void
    {
        $user = User::factory()->create();
        $provider = Provider::create([
            'code' => 'smsbower',
            'name' => 'SMSBower',
            'base_url' => 'https://smsbower.app/stubs/handler_api.php',
            'is_active' => true,
        ]);
        $service = OtpService::create([
            'provider_id' => $provider->id,
            'provider_code' => 'wa',
            'name' => 'WhatsApp',
            'is_active' => true,
        ]);

        foreach (['Albania', 'Indonesia', 'Australia'] as $index => $countryName) {
            $country = Country::create([
                'provider_id' => $provider->id,
                'provider_code' => (string) $index,
                'name' => $countryName,
                'is_active' => true,
            ]);

            ServicePrice::create([
                'provider_id' => $provider->id,
                'otp_service_id' => $service->id,
                'country_id' => $country->id,
                'provider_price' => '0.2000',
                'selling_price' => '1000.00',
                'stock_count' => 12,
                'is_active' => true,
                'last_synced_at' => now(),
            ]);
        }

        $html = $this->actingAs($user)->get('/otp')->assertOk()->getContent();

        $this->assertLessThan(
            strpos($html, 'Albania'),
            strpos($html, 'Indonesia'),
        );
        $this->assertLessThan(
            strpos($html, 'Australia'),
            strpos($html, 'Indonesia'),
        );
    }

    public function test_country_search_filters_by_name_prefix(): void
    {
        $user = User::factory()->create();
        $provider = Provider::create([
            'code' => 'smsbower',
            'name' => 'SMSBower',
            'base_url' => 'https://smsbower.app/stubs/handler_api.php',
            'is_active' => true,
        ]);
        $service = OtpService::create([
            'provider_id' => $provider->id,
            'provider_code' => 'wa',
            'name' => 'WhatsApp',
            'is_active' => true,
        ]);

        foreach (['Albania', 'Australia', 'Indonesia'] as $index => $countryName) {
            $country = Country::create([
                'provider_id' => $provider->id,
                'provider_code' => (string) $index,
                'name' => $countryName,
                'is_active' => true,
            ]);

            ServicePrice::create([
                'provider_id' => $provider->id,
                'otp_service_id' => $service->id,
                'country_id' => $country->id,
                'provider_price' => '0.2000',
                'selling_price' => '1000.00',
                'stock_count' => 12,
                'is_active' => true,
                'last_synced_at' => now(),
            ]);
        }

        $this->actingAs($user)
            ->get('/otp?country_q=A')
            ->assertOk()
            ->assertSee('Albania')
            ->assertSee('Australia')
            ->assertDontSee('Indonesia');
    }

    public function test_service_search_filters_by_name_prefix(): void
    {
        $user = User::factory()->create();
        $provider = Provider::create([
            'code' => 'smsbower',
            'name' => 'SMSBower',
            'base_url' => 'https://smsbower.app/stubs/handler_api.php',
            'is_active' => true,
        ]);
        $country = Country::create([
            'provider_id' => $provider->id,
            'provider_code' => '6',
            'name' => 'Indonesia',
            'is_active' => true,
        ]);

        foreach (['WhatsApp', 'WeChat', 'Telegram'] as $index => $serviceName) {
            $service = OtpService::create([
                'provider_id' => $provider->id,
                'provider_code' => 'svc'.$index,
                'name' => $serviceName,
                'is_active' => true,
            ]);

            ServicePrice::create([
                'provider_id' => $provider->id,
                'otp_service_id' => $service->id,
                'country_id' => $country->id,
                'provider_price' => '0.2000',
                'selling_price' => '1000.00',
                'stock_count' => 12,
                'is_active' => true,
                'last_synced_at' => now(),
            ]);
        }

        $this->actingAs($user)
            ->get('/otp?country_id='.$country->id.'&service_q=W')
            ->assertOk()
            ->assertSee('WhatsApp')
            ->assertSee('WeChat')
            ->assertDontSee('Telegram');
    }

    public function test_reprice_catalog_command_uses_current_default_margin_when_requested(): void
    {
        config()->set('services.smsbower.default_margin_type', 'percent');
        config()->set('services.smsbower.default_margin_value', 10);
        config()->set('services.smsbower.usd_to_idr_rate', 17000);
        config()->set('services.smsbower.minimum_selling_price_idr', 500);
        config()->set('services.smsbower.minimum_profit_idr', 300);
        config()->set('services.smsbower.rounding_idr', 50);

        $provider = Provider::create(['code' => 'smsbower', 'name' => 'SMSBower', 'is_active' => true]);
        $service = OtpService::create(['provider_id' => $provider->id, 'provider_code' => 'wa', 'name' => 'WhatsApp']);
        $country = Country::create(['provider_id' => $provider->id, 'provider_code' => '6', 'name' => 'Indonesia']);
        $price = ServicePrice::create([
            'provider_id' => $provider->id,
            'otp_service_id' => $service->id,
            'country_id' => $country->id,
            'provider_price' => '0.0130',
            'margin_type' => 'percent',
            'margin_value' => '30.00',
            'selling_price' => '1900.00',
            'stock_count' => 12,
            'is_active' => true,
            'last_synced_at' => now(),
        ]);

        $this->artisan('smsbower:reprice-catalog', ['--use-default-margin' => true])
            ->assertSuccessful();

        $price->refresh();

        $this->assertSame('percent', $price->margin_type);
        $this->assertSame('10.00', $price->margin_value);
        $this->assertSame('550.00', $price->selling_price);
    }

    public function test_scoped_catalog_sync_updates_only_requested_service_and_country(): void
    {
        config()->set('services.smsbower.default_margin_type', 'percent');
        config()->set('services.smsbower.default_margin_value', 10);
        config()->set('services.smsbower.usd_to_idr_rate', 17000);
        config()->set('services.smsbower.minimum_selling_price_idr', 500);
        config()->set('services.smsbower.minimum_profit_idr', 300);
        config()->set('services.smsbower.rounding_idr', 50);

        $provider = Provider::create(['code' => 'smsbower', 'name' => 'SMSBower', 'is_active' => true]);
        $country = Country::create(['provider_id' => $provider->id, 'provider_code' => '6', 'name' => 'Indonesia']);
        $otherCountry = Country::create(['provider_id' => $provider->id, 'provider_code' => '7', 'name' => 'Malaysia']);
        $service = OtpService::create(['provider_id' => $provider->id, 'provider_code' => 'wa', 'name' => 'WhatsApp']);
        $otherService = OtpService::create(['provider_id' => $provider->id, 'provider_code' => 'tg', 'name' => 'Telegram']);

        ServicePrice::create([
            'provider_id' => $provider->id,
            'otp_service_id' => $otherService->id,
            'country_id' => $otherCountry->id,
            'provider_price' => '0.2000',
            'selling_price' => '9999.00',
            'stock_count' => 99,
            'is_active' => true,
            'last_synced_at' => now()->subDay(),
        ]);

        $this->app->instance(SmsProviderInterface::class, new class implements SmsProviderInterface
        {
            public function getBalance(): string
            {
                return '1.84';
            }

            public function getServices(): array
            {
                throw new RuntimeException('Services should not be synced in scoped mode.');
            }

            public function getCountries(): array
            {
                throw new RuntimeException('Countries should not be synced in scoped mode.');
            }

            public function getPrices(?string $service = null, ?string $country = null): array
            {
                TestCase::assertSame('wa', $service);
                TestCase::assertSame('6', $country);

                return [
                    '6' => [
                        'wa' => [
                            'provider-1' => ['provider_id' => 3358, 'cost' => '0.0130', 'count' => 5],
                        ],
                    ],
                ];
            }

            public function requestNumber(string $service, string $country, ?string $maxPrice = null, ?string $providerId = null): array
            {
                return [];
            }

            public function getStatus(string $activationId): array
            {
                return [];
            }

            public function cancel(string $activationId): bool
            {
                return true;
            }

            public function complete(string $activationId): bool
            {
                return true;
            }
        });

        $this->artisan('smsbower:sync-catalog', ['--service' => 'wa', '--country' => '6'])
            ->assertSuccessful();

        $this->assertDatabaseHas('service_prices', [
            'provider_id' => $provider->id,
            'otp_service_id' => $service->id,
            'country_id' => $country->id,
            'provider_price_key' => 'provider:3358',
            'selling_price' => '550.00',
            'stock_count' => 5,
            'is_active' => true,
        ]);
        $this->assertDatabaseHas('service_prices', [
            'provider_id' => $provider->id,
            'otp_service_id' => $otherService->id,
            'country_id' => $otherCountry->id,
            'selling_price' => '9999.00',
            'stock_count' => 99,
            'is_active' => true,
        ]);
    }

    public function test_user_can_queue_refresh_for_current_catalog_scope(): void
    {
        Bus::fake();

        $user = User::factory()->create();
        $provider = Provider::create(['code' => 'smsbower', 'name' => 'SMSBower', 'is_active' => true]);
        $country = Country::create(['provider_id' => $provider->id, 'provider_code' => '6', 'name' => 'Indonesia']);
        $service = OtpService::create(['provider_id' => $provider->id, 'provider_code' => 'wa', 'name' => 'WhatsApp']);

        $this->actingAs($user)
            ->post(route('otp.refresh-current'), [
                'country_id' => $country->id,
                'service_id' => $service->id,
            ])
            ->assertRedirect()
            ->assertSessionHas('status');

        Bus::assertDispatched(SyncSmsbowerCatalogJob::class, fn (SyncSmsbowerCatalogJob $job): bool => $job->serviceCode === 'wa'
            && $job->countryCode === '6'
            && $job->syncLogId !== null);
        $this->assertDatabaseHas('provider_sync_logs', [
            'sync_type' => 'scoped',
            'country_code' => '6',
            'service_code' => 'wa',
            'status' => 'queued',
        ]);
    }

    public function test_user_scoped_refresh_uses_cooldown_for_same_scope(): void
    {
        Bus::fake();
        config()->set('services.smsbower.scope_sync_cooldown_seconds', 120);

        $user = User::factory()->create();
        $provider = Provider::create(['code' => 'smsbower', 'name' => 'SMSBower', 'is_active' => true]);
        $country = Country::create(['provider_id' => $provider->id, 'provider_code' => '6', 'name' => 'Indonesia']);
        $service = OtpService::create(['provider_id' => $provider->id, 'provider_code' => 'wa', 'name' => 'WhatsApp']);

        app(ProviderSyncTracker::class)->markQueued('wa', '6', $user->id);

        $this->actingAs($user)
            ->post(route('otp.refresh-current'), [
                'country_id' => $country->id,
                'service_id' => $service->id,
            ])
            ->assertRedirect()
            ->assertSessionHas('status');

        Bus::assertNotDispatched(SyncSmsbowerCatalogJob::class);
    }
}
