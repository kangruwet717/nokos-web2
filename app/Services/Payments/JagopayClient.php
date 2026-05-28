<?php

namespace App\Services\Payments;

use App\Models\PaymentInvoice;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class JagopayClient implements PaymentGatewayInterface
{
    public function createCheckout(array $payload, string $idempotencyKey): array
    {
        $amount = (int) ($payload['amount'] ?? 0);
        $reference = (string) ($payload['reference'] ?? $idempotencyKey);

        $response = $this->request([
            'action' => 'qris_dinamis',
            'nominal' => $amount,
        ]);

        $data = $response['data'] ?? [];

        return [
            'id' => $reference,
            'status' => 'pending',
            'type' => 'qris2',
            'amount' => $amount,
            'reference' => $reference,
            'qr_string' => $data['qris_string'] ?? null,
            'qr_url' => $data['qris_url'] ?? null,
            'expiresAt' => now()->addMinutes((int) config('services.jagopay.expiry_minutes', 15))->toISOString(),
            'raw' => $response,
        ];
    }

    public function getCheckoutDetail(string $checkoutId): array
    {
        return $this->checkCheckoutStatus($checkoutId);
    }

    public function checkCheckoutStatus(string $checkoutId): array
    {
        $invoice = PaymentInvoice::query()
            ->where('invoice_no', $checkoutId)
            ->orWhere('external_id', $checkoutId)
            ->first();

        if (! $invoice) {
            return ['id' => $checkoutId, 'status' => 'pending'];
        }

        $mutation = $this->findMatchingMutation($invoice);

        if (! $mutation) {
            return [
                'id' => $checkoutId,
                'amount' => (float) $invoice->amount,
                'status' => 'pending',
                'type' => 'qris2',
            ];
        }

        return [
            'id' => (string) ($mutation['id'] ?? $checkoutId),
            'amount' => (float) $invoice->amount,
            'status' => 'paid',
            'type' => 'qris2',
            'mutation' => $mutation,
        ];
    }

    public function cancelCheckout(string $checkoutId): array
    {
        return ['id' => $checkoutId, 'status' => 'cancelled'];
    }

    private function findMatchingMutation(PaymentInvoice $invoice): ?array
    {
        $pages = max((int) config('services.jagopay.mutation_pages', 3), 1);
        $expectedAmount = (int) round((float) $invoice->amount);

        for ($page = 1; $page <= $pages; $page++) {
            $response = $this->request([
                'action' => 'qris_mutasi',
                'page' => $page,
            ]);

            foreach (data_get($response, 'data.mutasi', []) as $mutation) {
                if (! is_array($mutation)) {
                    continue;
                }

                $amount = $this->normalizeAmount((string) ($mutation['kredit'] ?? '0'));
                $status = strtoupper((string) ($mutation['status'] ?? ''));

                if ($status !== 'IN' || $amount !== $expectedAmount) {
                    continue;
                }

                if ($this->mutationIsBeforeInvoice($mutation, $invoice)) {
                    continue;
                }

                return $mutation;
            }
        }

        return null;
    }

    private function mutationIsBeforeInvoice(array $mutation, PaymentInvoice $invoice): bool
    {
        $date = $mutation['tanggal'] ?? null;

        if (! $date) {
            return false;
        }

        try {
            $mutationTime = Carbon::createFromFormat('d/m/Y H:i:s', (string) $date);
        } catch (\Throwable) {
            return false;
        }

        return $mutationTime->lt($invoice->created_at->copy()->subMinutes(2));
    }

    private function normalizeAmount(string $amount): int
    {
        $normalized = preg_replace('/[^0-9]/', '', $amount);

        return (int) ($normalized ?: 0);
    }

    private function request(array $query): array
    {
        $apiKey = (string) config('services.jagopay.api_key');

        if ($apiKey === '') {
            throw new RuntimeException('JAGOPAY_API_KEY is not configured.');
        }

        $response = $this->http()->get('/api.php', array_merge($query, [
            'apikey' => $apiKey,
        ]));

        if ($response->failed()) {
            throw new RuntimeException("Jagopay request failed [{$response->status()}]: ".$response->body());
        }

        $json = $response->json();

        if (! is_array($json)) {
            throw new RuntimeException('Jagopay returned invalid JSON response.');
        }

        return $json;
    }

    private function http(): PendingRequest
    {
        return Http::baseUrl((string) config('services.jagopay.base_url'))
            ->timeout((int) config('services.jagopay.timeout', 20))
            ->acceptJson();
    }
}
