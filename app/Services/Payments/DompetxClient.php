<?php

namespace App\Services\Payments;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class DompetxClient implements PaymentGatewayInterface
{
    public function createCheckout(array $payload, string $idempotencyKey): array
    {
        return $this->request('post', '/v1/payments/checkout', $payload, $idempotencyKey);
    }

    public function getCheckoutDetail(string $checkoutId): array
    {
        return $this->request('get', "/v1/payments/checkout/detail/{$checkoutId}");
    }

    public function checkCheckoutStatus(string $checkoutId): array
    {
        return $this->request('get', "/v1/payments/checkout/check-status/{$checkoutId}");
    }

    public function cancelCheckout(string $checkoutId): array
    {
        return $this->request('post', "/v1/payments/checkout/cancel/{$checkoutId}", []);
    }

    private function request(string $method, string $path, array $payload = [], ?string $idempotencyKey = null): array
    {
        $body = $method === 'get' ? '{}' : json_encode($payload, JSON_UNESCAPED_SLASHES);
        $timestamp = (string) time();

        $response = $this->http($timestamp, $body, $idempotencyKey)->{$method}($path, $method === 'get' ? [] : $payload);

        if ($response->failed()) {
            throw new RuntimeException("DompetX request failed [{$response->status()}]: ".$response->body());
        }

        return $response->json() ?? [];
    }

    private function http(string $timestamp, string $body, ?string $idempotencyKey = null): PendingRequest
    {
        $apiKey = (string) config('services.dompetx.api_key');

        if ($apiKey === '') {
            throw new RuntimeException('DOMPETX_API_KEY is not configured.');
        }

        $headers = [
            'Content-Type' => 'application/json',
            'X-DOMPAY-API-Key' => $apiKey,
            'X-DOMPAY-Timestamp' => $timestamp,
            'X-DOMPAY-Signature' => hash_hmac('sha256', $timestamp.'.'.$body, $apiKey),
        ];

        if ($idempotencyKey) {
            $headers['Idempotency-Key'] = $idempotencyKey;
        }

        return Http::baseUrl((string) config('services.dompetx.base_url'))
            ->timeout((int) config('services.dompetx.timeout'))
            ->acceptJson()
            ->withHeaders($headers);
    }
}
