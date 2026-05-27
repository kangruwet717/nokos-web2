<?php

namespace App\Http\Controllers;

use App\Models\PaymentInvoice;
use App\Models\PaymentWebhookEvent;
use App\Services\Payments\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class DompetxWebhookController extends Controller
{
    public function __invoke(Request $request, PaymentService $payments): JsonResponse
    {
        $payload = $request->all();
        $data = $payload['data'] ?? [];
        $externalId = $payload['paymentId'] ?? $data['id'] ?? null;
        $eventType = $payload['eventType'] ?? 'payment.status';
        $eventId = $externalId ? "{$externalId}:{$eventType}:".($data['status'] ?? 'unknown') : null;
        $invoice = $this->findInvoice($data['reference'] ?? null, $externalId);
        $signatureValid = $this->hasAcceptableSignature($request);

        if (! $signatureValid) {
            PaymentWebhookEvent::create([
                'provider' => 'dompetx',
                'event_id' => $eventId ? "{$eventId}:invalid:".Str::uuid() : null,
                'external_id' => $externalId,
                'invoice_id' => $invoice?->id,
                'event_type' => $eventType,
                'payload' => $payload,
                'signature_valid' => false,
                'error_message' => 'Invalid DompetX webhook signature.',
            ]);

            return response()->json(['ok' => false], 401);
        }

        $event = PaymentWebhookEvent::firstOrCreate(
            [
                'provider' => 'dompetx',
                'event_id' => $eventId,
            ],
            [
                'external_id' => $externalId,
                'invoice_id' => $invoice?->id,
                'event_type' => $eventType,
                'payload' => $payload,
                'signature_valid' => true,
            ],
        );

        if (! $event->processed) {
            try {
                $payments->processWebhook($event);
            } catch (\Throwable $exception) {
                $event->forceFill(['error_message' => $exception->getMessage()])->save();

                report($exception);
            }
        }

        return response()->json(['ok' => true]);
    }

    private function findInvoice(?string $reference, ?string $externalId): ?PaymentInvoice
    {
        if (! $reference && ! $externalId) {
            return null;
        }

        return PaymentInvoice::query()
            ->where(function ($query) use ($reference, $externalId) {
                $query
                    ->when($reference, fn ($query) => $query->orWhere('invoice_no', $reference))
                    ->when($externalId, fn ($query) => $query->orWhere('external_id', $externalId));
            })
            ->first();
    }

    private function hasAcceptableSignature(Request $request): bool
    {
        $secret = config('services.dompetx.webhook_secret') ?: config('services.dompetx.api_key');

        if (! $secret) {
            return true;
        }

        $signature = $request->header('X-DOMPAY-Signature');
        $timestamp = $request->header('X-DOMPAY-Timestamp');

        if (! $signature || ! $timestamp) {
            return false;
        }

        if (! ctype_digit((string) $timestamp)) {
            return false;
        }

        $tolerance = (int) config('services.dompetx.webhook_tolerance_seconds', 300);
        if ($tolerance > 0 && abs(time() - (int) $timestamp) > $tolerance) {
            return false;
        }

        return hash_equals(hash_hmac('sha256', $timestamp.'.'.$request->getContent(), $secret), $signature);
    }
}
