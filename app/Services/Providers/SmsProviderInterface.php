<?php

namespace App\Services\Providers;

interface SmsProviderInterface
{
    public function getBalance(): string;

    public function getServices(): array;

    public function getCountries(): array;

    public function getPrices(?string $service = null, ?string $country = null): array;

    public function requestNumber(string $service, string $country, ?string $maxPrice = null, ?string $providerId = null): array;

    public function getStatus(string $activationId): array;

    public function cancel(string $activationId): bool;

    public function complete(string $activationId): bool;
}
