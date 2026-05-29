<?php

namespace App\Services\Providers;

use App\Models\Country;
use App\Models\OtpService;
use App\Models\Provider;
use App\Models\ServicePrice;
use App\Services\Pricing\SmsbowerPricingService;
use App\Support\CountryFlag;
use Illuminate\Support\Facades\DB;

class SmsbowerCatalogSyncService
{
    private const PRICE_UPSERT_CHUNK_SIZE = 1000;

    public function __construct(
        private readonly SmsProviderInterface $client,
        private readonly SmsbowerPricingService $pricing,
    ) {}

    public function sync(?string $serviceCode = null, ?string $countryCode = null): array
    {
        $provider = $this->provider();
        $services = $serviceCode ? 0 : $this->syncServices($provider);
        $countries = $countryCode ? 0 : $this->syncCountries($provider);
        $prices = $this->syncPrices($provider, $serviceCode, $countryCode);
        $balance = $this->syncBalance($provider);

        return compact('services', 'countries', 'prices', 'balance');
    }

    public function syncBalance(?Provider $provider = null): string
    {
        $provider ??= $this->provider();
        $balance = $this->client->getBalance();

        $provider->forceFill([
            'last_balance' => $balance,
            'balance_checked_at' => now(),
        ])->save();

        return $balance;
    }

    public function syncServices(?Provider $provider = null): int
    {
        $provider ??= $this->provider();
        $count = 0;

        foreach ($this->client->getServices() as $service) {
            if (! isset($service['code'])) {
                continue;
            }

            OtpService::updateOrCreate(
                ['provider_id' => $provider->id, 'provider_code' => (string) $service['code']],
                ['name' => $service['name'] ?? $service['code'], 'is_active' => true],
            );

            $count++;
        }

        return $count;
    }

    public function syncCountries(?Provider $provider = null): int
    {
        $provider ??= $this->provider();
        $count = 0;

        foreach ($this->client->getCountries() as $code => $country) {
            if (! is_array($country)) {
                continue;
            }

            $name = $country['eng'] ?? $country['rus'] ?? (string) $code;

            Country::updateOrCreate(
                ['provider_id' => $provider->id, 'provider_code' => (string) ($country['id'] ?? $code)],
                [
                    'iso_code' => CountryFlag::isoCode($name, $country['iso'] ?? $country['iso_code'] ?? null),
                    'name' => $name,
                    'is_active' => true,
                ],
            );

            $count++;
        }

        return $count;
    }

    public function syncPrices(?Provider $provider = null, ?string $serviceCode = null, ?string $countryCode = null): int
    {
        $provider ??= $this->provider();
        $synced = 0;
        $prices = $this->normalizePricesPayload(
            $this->client->getPrices($serviceCode, $countryCode),
            $serviceCode,
            $countryCode,
        );
        [$countryMap, $serviceMap] = $this->ensureCatalogReferences($provider, $prices);

        DB::transaction(function () use ($prices, $provider, $countryMap, $serviceMap, $serviceCode, $countryCode, &$synced) {
            $now = now();
            $marginType = (string) config('services.smsbower.default_margin_type', 'percent');
            $marginValue = (string) config('services.smsbower.default_margin_value', 30);
            $rows = [];
            $flush = function () use (&$rows): void {
                if ($rows === []) {
                    return;
                }

                ServicePrice::query()->upsert(
                    array_values($rows),
                    ['provider_id', 'otp_service_id', 'country_id', 'provider_price_key'],
                    [
                        'provider_price',
                        'provider_meta',
                        'margin_type',
                        'margin_value',
                        'selling_price',
                        'stock_count',
                        'is_active',
                        'last_synced_at',
                        'updated_at',
                    ],
                );

                $rows = [];
            };

            ServicePrice::query()
                ->where('provider_id', $provider->id)
                ->when($countryCode, fn ($query) => $query->whereHas('country', fn ($countryQuery) => $countryQuery->where('provider_code', $countryCode)))
                ->when($serviceCode, fn ($query) => $query->whereHas('otpService', fn ($serviceQuery) => $serviceQuery->where('provider_code', $serviceCode)))
                ->update([
                    'stock_count' => 0,
                    'is_active' => false,
                ]);

            foreach ($prices as $countryCode => $services) {
                if (! is_array($services)) {
                    continue;
                }

                $countryId = $countryMap[(string) $countryCode] ?? null;
                if (! $countryId) {
                    continue;
                }

                foreach ($services as $serviceCode => $priceData) {
                    if (! is_array($priceData)) {
                        continue;
                    }

                    $serviceId = $serviceMap[(string) $serviceCode] ?? null;
                    if (! $serviceId) {
                        continue;
                    }

                    foreach ($this->priceVariants($priceData) as $variant) {
                        $providerPrice = $variant['provider_price'];
                        $stock = $variant['stock_count'];

                        $rowKey = implode(':', [$provider->id, $serviceId, $countryId, $variant['key']]);

                        $rows[$rowKey] = [
                            'provider_id' => $provider->id,
                            'otp_service_id' => $serviceId,
                            'country_id' => $countryId,
                            'provider_price_key' => $variant['key'],
                            'provider_price' => $providerPrice,
                            'provider_meta' => json_encode($variant['meta']),
                            'margin_type' => $marginType,
                            'margin_value' => $marginValue,
                            'selling_price' => $this->pricing->sellingPriceIdr($providerPrice, $marginType, $marginValue),
                            'stock_count' => $stock,
                            'is_active' => $stock > 0,
                            'last_synced_at' => $now,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ];

                        $synced++;

                        if (count($rows) >= self::PRICE_UPSERT_CHUNK_SIZE) {
                            $flush();
                        }
                    }
                }
            }

            $flush();
        });

        return $synced;
    }

    private function normalizePricesPayload(array $prices, ?string $serviceCode, ?string $countryCode): array
    {
        if (! $serviceCode && ! $countryCode) {
            return $prices;
        }

        if ($countryCode && $serviceCode && isset($prices[$countryCode][$serviceCode])) {
            return [
                $countryCode => [
                    $serviceCode => $prices[$countryCode][$serviceCode],
                ],
            ];
        }

        if ($countryCode && $serviceCode && isset($prices[$serviceCode])) {
            return [
                $countryCode => [
                    $serviceCode => $prices[$serviceCode],
                ],
            ];
        }

        if ($countryCode && $serviceCode && (isset($prices['cost']) || $this->looksLikeVariantList($prices))) {
            return [
                $countryCode => [
                    $serviceCode => $prices,
                ],
            ];
        }

        if ($countryCode && isset($prices[$countryCode])) {
            return [$countryCode => $prices[$countryCode]];
        }

        if ($serviceCode) {
            $filtered = [];

            foreach ($prices as $payloadCountryCode => $services) {
                if (is_array($services) && isset($services[$serviceCode])) {
                    $filtered[(string) $payloadCountryCode] = [$serviceCode => $services[$serviceCode]];
                }
            }

            return $filtered;
        }

        return $prices;
    }

    private function looksLikeVariantList(array $prices): bool
    {
        foreach ($prices as $key => $value) {
            if ((is_array($value) && (isset($value['price']) || isset($value['cost']))) || (is_numeric($key) && is_numeric($value))) {
                return true;
            }
        }

        return false;
    }

    private function ensureCatalogReferences(Provider $provider, array $prices): array
    {
        $now = now();
        $countryRows = [];
        $serviceRows = [];

        foreach ($prices as $countryCode => $services) {
            if (! is_array($services)) {
                continue;
            }

            $countryRows[(string) $countryCode] = [
                'provider_id' => $provider->id,
                'provider_code' => (string) $countryCode,
                'name' => (string) $countryCode,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ];

            foreach ($services as $serviceCode => $priceData) {
                if (! is_array($priceData)) {
                    continue;
                }

                $serviceRows[(string) $serviceCode] = [
                    'provider_id' => $provider->id,
                    'provider_code' => (string) $serviceCode,
                    'name' => (string) $serviceCode,
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        if ($countryRows !== []) {
            Country::query()->upsert(
                array_values($countryRows),
                ['provider_id', 'provider_code'],
                ['updated_at'],
            );
        }

        if ($serviceRows !== []) {
            OtpService::query()->upsert(
                array_values($serviceRows),
                ['provider_id', 'provider_code'],
                ['updated_at'],
            );
        }

        $countryMap = Country::query()
            ->where('provider_id', $provider->id)
            ->whereIn('provider_code', array_keys($countryRows))
            ->pluck('id', 'provider_code')
            ->all();

        $serviceMap = OtpService::query()
            ->where('provider_id', $provider->id)
            ->whereIn('provider_code', array_keys($serviceRows))
            ->pluck('id', 'provider_code')
            ->all();

        return [$countryMap, $serviceMap];
    }

    public function provider(): Provider
    {
        return Provider::firstOrCreate(
            ['code' => 'smsbower'],
            [
                'name' => 'SMSBower',
                'base_url' => config('services.smsbower.base_url'),
                'is_active' => true,
            ],
        );
    }

    private function priceVariants(array $priceData): array
    {
        if (isset($priceData['cost'])) {
            $providerId = $priceData['provider_id'] ?? null;

            return [[
                'key' => $providerId ? 'provider:'.$providerId : 'default',
                'provider_price' => (string) $priceData['cost'],
                'stock_count' => (int) ($priceData['count'] ?? 0),
                'meta' => $priceData,
            ]];
        }

        $variants = [];

        foreach ($priceData as $key => $value) {
            if (is_array($value) && (isset($value['price']) || isset($value['cost']))) {
                $providerId = (string) ($value['provider_id'] ?? $key);

                $variants[] = [
                    'key' => 'provider:'.$providerId,
                    'provider_price' => (string) ($value['cost'] ?? $value['price']),
                    'stock_count' => (int) ($value['count'] ?? 0),
                    'meta' => array_merge($value, ['provider_id' => $providerId]),
                ];

                continue;
            }

            if (is_numeric($key) && is_numeric($value)) {
                $variants[] = [
                    'key' => 'price:'.$key,
                    'provider_price' => (string) $key,
                    'stock_count' => (int) $value,
                    'meta' => ['price' => (string) $key],
                ];
            }
        }

        return $variants;
    }
}
