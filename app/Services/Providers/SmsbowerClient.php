<?php

namespace App\Services\Providers;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class SmsbowerClient implements SmsProviderInterface
{
    public function getBalance(): string
    {
        $response = $this->request(['action' => 'getBalance']);

        if (! str_starts_with($response, 'ACCESS_BALANCE:')) {
            $this->throwProviderError($response);
        }

        return explode(':', $response, 2)[1];
    }

    public function getServices(): array
    {
        $response = $this->request(['action' => 'getServicesList'], true);

        return $response['services'] ?? [];
    }

    public function getCountries(): array
    {
        $response = $this->request(['action' => 'getCountries'], true);

        return $response;
    }

    public function getPrices(?string $service = null, ?string $country = null): array
    {
        $query = ['action' => config('services.smsbower.prices_action', 'getPricesV3')];

        if ($service) {
            $query['service'] = $service;
        }

        if ($country) {
            $query['country'] = $country;
        }

        return $this->request($query, true);
    }

    public function requestNumber(string $service, string $country, ?string $maxPrice = null, ?string $providerId = null): array
    {
        $query = [
            'action' => 'getNumberV2',
            'service' => $service,
            'country' => $country,
        ];

        if ($maxPrice) {
            $query['maxPrice'] = $maxPrice;
        }

        if ($providerId) {
            $query['providerIds'] = $providerId;
        }

        return $this->request($query, true);
    }

    public function getStatus(string $activationId): array
    {
        $response = $this->request([
            'action' => 'getStatus',
            'id' => $activationId,
        ]);

        if ($response === 'STATUS_WAIT_CODE') {
            return ['status' => 'waiting_sms'];
        }

        if ($response === 'STATUS_CANCEL') {
            return ['status' => 'cancelled'];
        }

        if (str_starts_with($response, 'STATUS_WAIT_RETRY:')) {
            return ['status' => 'waiting_retry', 'last_code' => explode(':', $response, 2)[1]];
        }

        if (str_starts_with($response, 'STATUS_OK:')) {
            return ['status' => 'success', 'code' => trim(explode(':', $response, 2)[1], " '")];
        }

        $this->throwProviderError($response);
    }

    public function cancel(string $activationId): bool
    {
        return $this->setStatus($activationId, 8) === 'ACCESS_CANCEL';
    }

    public function complete(string $activationId): bool
    {
        return $this->setStatus($activationId, 6) === 'ACCESS_ACTIVATION';
    }

    private function setStatus(string $activationId, int $status): string
    {
        $response = $this->request([
            'action' => 'setStatus',
            'id' => $activationId,
            'status' => $status,
        ]);

        if (str_starts_with($response, 'BAD_') || str_starts_with($response, 'NO_') || $response === 'EARLY_CANCEL_DENIED') {
            $this->throwProviderError($response);
        }

        return $response;
    }

    private function request(array $query, bool $json = false): array|string
    {
        $apiKey = (string) config('services.smsbower.api_key');

        if ($apiKey === '') {
            throw new RuntimeException('SMSBOWER_API_KEY is not configured.');
        }

        $urls = $this->baseUrls();
        $lastConnectionException = null;

        foreach ($urls as $url) {
            try {
                $response = Http::connectTimeout((int) config('services.smsbower.connect_timeout', 5))
                    ->timeout((int) config('services.smsbower.timeout'))
                    ->retry((int) config('services.smsbower.retry_attempts'), 250)
                    ->acceptJson()
                    ->get($url, array_merge($query, [
                        'api_key' => $apiKey,
                    ]));

                break;
            } catch (ConnectionException $exception) {
                $lastConnectionException = $exception;
                $response = null;
            }
        }

        if (! isset($response)) {
            throw new RuntimeException('SMSBower sedang tidak bisa dihubungi. Coba lagi beberapa saat atau cek koneksi server ke endpoint SMSBower.', previous: $lastConnectionException);
        }

        if ($response->failed()) {
            throw new RuntimeException("SMSBower request failed [{$response->status()}]: ".$response->body());
        }

        if ($json) {
            $payload = $response->json();

            if (! is_array($payload)) {
                $this->throwProviderError($response->body());
            }

            return $payload;
        }

        return trim($response->body());
    }

    private function baseUrls(): array
    {
        $urls = array_merge(
            [(string) config('services.smsbower.base_url')],
            config('services.smsbower.fallback_base_urls', []),
        );

        return array_values(array_unique(array_filter($urls)));
    }

    private function throwProviderError(string $response): never
    {
        throw new RuntimeException("SMSBower provider error: {$response}");
    }
}
