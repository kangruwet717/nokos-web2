<?php

namespace App\Http\Controllers;

use App\Models\OtpOrder;
use App\Models\ProviderWebhookEvent;
use App\Services\Orders\OtpOrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SmsbowerWebhookController extends Controller
{
    public function __invoke(Request $request, OtpOrderService $orders): JsonResponse
    {
        $payload = $request->all();
        $activationId = (string) ($payload['activationId'] ?? '');
        $eventId = $activationId ? $activationId.':'.($payload['code'] ?? 'sms') : null;
        $order = $activationId
            ? OtpOrder::where('provider_activation_id', $activationId)->first()
            : null;

        $event = ProviderWebhookEvent::firstOrCreate(
            [
                'provider' => 'smsbower',
                'event_id' => $eventId,
            ],
            [
                'activation_id' => $activationId ?: null,
                'otp_order_id' => $order?->id,
                'payload' => $payload,
                'signature_valid' => $this->hasAcceptableSignature($request),
            ],
        );

        if (! $event->processed && $event->signature_valid) {
            try {
                $orders->processWebhook($event);
            } catch (\Throwable $exception) {
                $event->forceFill(['error_message' => $exception->getMessage()])->save();
                report($exception);
            }
        }

        return response()->json(['ok' => true]);
    }

    private function hasAcceptableSignature(Request $request): bool
    {
        $secret = config('services.smsbower.webhook_secret');

        if (! $secret) {
            return true;
        }

        return hash_equals($secret, (string) $request->header('X-SMSBOWER-Webhook-Secret'));
    }
}
