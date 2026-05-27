<?php

namespace App\Services\Orders;

use App\Models\OtpOrder;
use App\Models\ProviderWebhookEvent;
use App\Models\ServicePrice;
use App\Models\User;
use App\Services\Audit\AuditLogService;
use App\Services\Pricing\SmsbowerPricingService;
use App\Services\Providers\SmsProviderInterface;
use App\Services\Wallet\WalletService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class OtpOrderService
{
    public function __construct(
        private readonly SmsProviderInterface $provider,
        private readonly WalletService $wallet,
        private readonly AuditLogService $audit,
        private readonly SmsbowerPricingService $pricing,
    ) {}

    public function create(User $user, ServicePrice $price): OtpOrder
    {
        $price->loadMissing(['provider', 'otpService', 'country']);
        $price = $this->refreshPriceBeforeOrder($price);

        if (! $price->is_active || ! $price->otpService->is_active || ! $price->country->is_active) {
            throw new RuntimeException('Service ini sedang tidak tersedia.');
        }

        if ($price->otpService->is_blacklisted || $price->country->is_blacklisted) {
            throw new RuntimeException('Service atau country ini tidak tersedia.');
        }

        $maxActiveOrders = (int) env('OTP_MAX_ACTIVE_ORDERS_PER_USER', 3);

        if ($user->otpOrders()->where('status', 'waiting_sms')->count() >= $maxActiveOrders) {
            throw new RuntimeException('Masih ada terlalu banyak order aktif. Tunggu atau batalkan salah satunya.');
        }

        $order = DB::transaction(function () use ($user, $price): OtpOrder {
            $order = OtpOrder::create([
                'order_no' => $this->generateOrderNo(),
                'user_id' => $user->id,
                'provider_id' => $price->provider_id,
                'otp_service_id' => $price->otp_service_id,
                'country_id' => $price->country_id,
                'provider_cost' => $price->provider_price,
                'selling_price' => $price->selling_price,
                'margin_amount' => bcsub((string) $price->selling_price, $this->pricing->providerCostIdr((string) $price->provider_price), 2),
                'status' => 'creating',
                'expires_at' => now()->addMinutes((int) env('OTP_ORDER_TIMEOUT_MINUTES', 20)),
            ]);

            $this->logStatus($order, null, 'creating', 'system', 'Order created.');
            $this->wallet->hold($user, (string) $order->selling_price, 'order_hold', $order, "Hold {$order->order_no}");

            return $order;
        });

        try {
            $response = $this->provider->requestNumber(
                $price->otpService->provider_code,
                $price->country->provider_code,
                (string) $price->provider_price,
                $this->providerId($price),
            );
        } catch (\Throwable $exception) {
            $this->markFailed($order, $exception->getMessage());

            throw $exception;
        }

        if (! isset($response['activationId'], $response['phoneNumber'])) {
            $this->markFailed($order, 'Provider did not return activation data.');

            throw new RuntimeException('Provider tidak memberi nomor. Coba lagi nanti.');
        }

        $this->transition($order, 'waiting_sms', 'provider', 'Number assigned.', [
            'activation_id' => $response['activationId'],
        ]);

        $order->forceFill([
            'provider_activation_id' => (string) $response['activationId'],
            'phone_number' => (string) $response['phoneNumber'],
            'phone_number_masked' => $this->maskPhone((string) $response['phoneNumber']),
            'provider_cost' => $response['activationCost'] ?? $order->provider_cost,
            'raw_provider_response' => $response,
        ])->save();

        $this->audit->record('otp.order_created', $user, $order, [
            'service' => $price->otpService->provider_code,
            'country' => $price->country->provider_code,
        ]);

        return $order->refresh();
    }

    public function refreshStatus(OtpOrder $order): OtpOrder
    {
        if ($order->status !== 'waiting_sms' || blank($order->provider_activation_id)) {
            return $order;
        }

        if ($order->expires_at && now()->greaterThan($order->expires_at)) {
            $this->expire($order);

            return $order->refresh();
        }

        $status = $this->provider->getStatus($order->provider_activation_id);

        if (($status['status'] ?? null) === 'success') {
            $this->markSuccess($order, (string) $status['code'], null, 'provider');
        } elseif (($status['status'] ?? null) === 'cancelled') {
            $this->markCancelled($order, 'Provider cancelled activation.', 'provider');
        }

        return $order->refresh();
    }

    public function cancel(OtpOrder $order, User $actor): OtpOrder
    {
        if (! $order->canBeCancelled()) {
            throw new RuntimeException('Order tidak dapat dibatalkan.');
        }

        if (! $this->provider->cancel((string) $order->provider_activation_id)) {
            throw new RuntimeException('Provider menolak pembatalan order.');
        }

        $this->markCancelled($order, 'Cancelled by user.', 'user');
        $this->audit->record('otp.order_cancelled', $actor, $order);

        return $order->refresh();
    }

    public function manualRefund(OtpOrder $order, User $admin, string $reason): OtpOrder
    {
        if (trim($reason) === '') {
            throw new RuntimeException('Alasan refund wajib diisi.');
        }

        if (! in_array($order->status, ['success', 'failed', 'expired', 'cancelled'], true)) {
            throw new RuntimeException('Order belum eligible untuk refund manual.');
        }

        if ($order->refunded_at || $order->status === 'refunded') {
            throw new RuntimeException('Order sudah pernah direfund.');
        }

        $this->wallet->credit(
            $order->user,
            (string) $order->selling_price,
            'refund',
            $order,
            "Manual refund {$order->order_no}: {$reason}",
            ['reason' => $reason],
            $admin,
        );

        $this->transition($order, 'refunded', 'admin', $reason);
        $order->forceFill([
            'refunded_at' => now(),
            'refund_reason' => $reason,
        ])->save();

        $this->audit->record('otp.order_refunded', $admin, $order, [
            'reason' => $reason,
            'amount' => $order->selling_price,
        ]);

        return $order->refresh();
    }

    public function refreshWaitingBatch(int $limit = 50): int
    {
        $count = 0;

        OtpOrder::query()
            ->where('status', 'waiting_sms')
            ->oldest()
            ->limit($limit)
            ->get()
            ->each(function (OtpOrder $order) use (&$count): void {
                try {
                    $this->refreshStatus($order);
                    $count++;
                } catch (\Throwable $exception) {
                    report($exception);
                }
            });

        return $count;
    }

    public function processWebhook(ProviderWebhookEvent $event): void
    {
        DB::transaction(function () use ($event) {
            /** @var ProviderWebhookEvent $lockedEvent */
            $lockedEvent = ProviderWebhookEvent::query()->whereKey($event->id)->lockForUpdate()->firstOrFail();

            if ($lockedEvent->processed) {
                return;
            }

            $payload = $lockedEvent->payload;
            $activationId = (string) ($payload['activationId'] ?? $lockedEvent->activation_id);

            /** @var OtpOrder|null $order */
            $order = OtpOrder::query()
                ->where('provider_activation_id', $activationId)
                ->lockForUpdate()
                ->first();

            if (! $order) {
                $lockedEvent->forceFill([
                    'error_message' => 'Order not found for provider webhook.',
                    'processed' => true,
                    'processed_at' => now(),
                ])->save();

                return;
            }

            $lockedEvent->forceFill(['otp_order_id' => $order->id])->save();

            if ($order->status === 'waiting_sms') {
                $this->markSuccess($order, (string) ($payload['code'] ?? ''), $payload['text'] ?? null, 'webhook');
            }

            $lockedEvent->forceFill([
                'processed' => true,
                'processed_at' => now(),
            ])->save();
        });
    }

    private function markSuccess(OtpOrder $order, string $code, ?string $text, string $source): void
    {
        if ($order->status !== 'waiting_sms') {
            return;
        }

        $this->wallet->charge($order->user, (string) $order->selling_price, 'order_charge', $order, "Charge {$order->order_no}");

        try {
            $this->provider->complete((string) $order->provider_activation_id);
        } catch (\Throwable $exception) {
            report($exception);
        }

        $this->transition($order, 'success', $source, 'OTP received.');

        $order->forceFill([
            'sms_code' => $code,
            'sms_text_masked' => $text ? $this->maskSmsText($text, $code) : null,
            'completed_at' => now(),
        ])->save();

    }

    private function markCancelled(OtpOrder $order, string $message, string $source): void
    {
        if (! in_array($order->status, ['creating', 'waiting_sms'], true)) {
            return;
        }

        $this->wallet->release($order->user, (string) $order->selling_price, 'refund', $order, "Release {$order->order_no}");
        $this->transition($order, 'cancelled', $source, $message);
        $order->forceFill(['cancelled_at' => now()])->save();
    }

    private function expire(OtpOrder $order): void
    {
        if ($order->status !== 'waiting_sms') {
            return;
        }

        try {
            $this->provider->cancel((string) $order->provider_activation_id);
        } catch (\Throwable $exception) {
            report($exception);
        }

        $this->wallet->release($order->user, (string) $order->selling_price, 'refund', $order, "Expire {$order->order_no}");
        $this->transition($order, 'expired', 'job', 'Order expired.');
    }

    private function markFailed(OtpOrder $order, string $message): void
    {
        $this->wallet->release($order->user, (string) $order->selling_price, 'refund', $order, "Release failed {$order->order_no}");
        $this->transition($order, 'failed', 'provider', $message);
    }

    private function transition(OtpOrder $order, string $status, string $source, ?string $message = null, array $metadata = []): void
    {
        $oldStatus = $order->status;
        $order->forceFill(['status' => $status])->save();
        $this->logStatus($order, $oldStatus, $status, $source, $message, $metadata);
    }

    private function logStatus(OtpOrder $order, ?string $oldStatus, string $newStatus, string $source, ?string $message = null, array $metadata = []): void
    {
        $order->statusLogs()->create([
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'source' => $source,
            'message' => $message,
            'metadata' => $metadata ?: null,
        ]);
    }

    private function maskPhone(string $phone): string
    {
        if (strlen($phone) <= 6) {
            return str_repeat('*', strlen($phone));
        }

        return substr($phone, 0, 3).str_repeat('*', max(strlen($phone) - 6, 0)).substr($phone, -3);
    }

    private function maskSmsText(string $text, string $code): string
    {
        return $code !== '' ? str_replace($code, str_repeat('*', strlen($code)), $text) : $text;
    }

    private function refreshPriceBeforeOrder(ServicePrice $price): ServicePrice
    {
        if (! config('services.smsbower.refresh_price_before_order')) {
            return $price;
        }

        $previousSellingPrice = (string) $price->selling_price;
        $priceData = $this->latestProviderPrice($price);

        if (! $priceData || ! isset($priceData['cost'])) {
            throw new RuntimeException('Harga provider belum tersedia. Coba lagi beberapa saat.');
        }

        $providerPrice = (string) $priceData['cost'];
        $stock = (int) ($priceData['count'] ?? 0);
        $sellingPrice = $this->pricing->sellingPriceIdr($providerPrice, (string) $price->margin_type, (string) $price->margin_value);

        $price->forceFill([
            'provider_price_key' => $this->variantKey($priceData),
            'provider_price' => $providerPrice,
            'provider_meta' => $priceData,
            'selling_price' => $sellingPrice,
            'stock_count' => $stock,
            'is_active' => $stock > 0,
            'last_synced_at' => now(),
        ])->save();

        if ($stock < 1) {
            throw new RuntimeException('Stok provider habis. Pilih service atau country lain.');
        }

        if (bccomp($previousSellingPrice, $sellingPrice, 2) !== 0) {
            throw new RuntimeException('Harga berubah. Muat ulang katalog lalu coba order lagi.');
        }

        return $price->refresh()->loadMissing(['provider', 'otpService', 'country']);
    }

    private function latestProviderPrice(ServicePrice $price): ?array
    {
        $serviceCode = (string) $price->otpService->provider_code;
        $countryCode = (string) $price->country->provider_code;
        $prices = $this->provider->getPrices($serviceCode, $countryCode);

        if (isset($prices[$countryCode][$serviceCode]) && is_array($prices[$countryCode][$serviceCode])) {
            return $this->matchingPriceVariant($prices[$countryCode][$serviceCode], $price->provider_price_key);
        }

        if (isset($prices[$serviceCode]) && is_array($prices[$serviceCode])) {
            return $this->matchingPriceVariant($prices[$serviceCode], $price->provider_price_key);
        }

        if (isset($prices['cost'])) {
            return $prices;
        }

        return null;
    }

    private function matchingPriceVariant(array $priceData, string $priceKey): ?array
    {
        if (isset($priceData['cost'])) {
            return $this->variantKey($priceData) === $priceKey ? $priceData : null;
        }

        foreach ($priceData as $key => $value) {
            if (is_array($value) && (isset($value['price']) || isset($value['cost']))) {
                $providerId = (string) ($value['provider_id'] ?? $key);
                $variantKey = 'provider:'.$providerId;

                if ($variantKey === $priceKey) {
                    return array_merge($value, [
                        'cost' => $value['cost'] ?? $value['price'],
                        'provider_id' => $providerId,
                    ]);
                }
            }

            if (is_numeric($key) && is_numeric($value) && 'price:'.$key === $priceKey) {
                return [
                    'cost' => (string) $key,
                    'count' => (int) $value,
                    'price' => (string) $key,
                ];
            }
        }

        return null;
    }

    private function variantKey(array $priceData): string
    {
        if (isset($priceData['provider_id'])) {
            return 'provider:'.$priceData['provider_id'];
        }

        if (isset($priceData['price']) && ! isset($priceData['cost'])) {
            return 'price:'.$priceData['price'];
        }

        return (string) ($priceData['provider_id'] ?? 'default');
    }

    private function providerId(ServicePrice $price): ?string
    {
        $providerId = $price->provider_meta['provider_id'] ?? null;

        return $providerId ? (string) $providerId : null;
    }

    private function generateOrderNo(): string
    {
        do {
            $orderNo = 'OTP-'.now()->format('YmdHis').'-'.Str::upper(Str::random(6));
        } while (OtpOrder::where('order_no', $orderNo)->exists());

        return $orderNo;
    }
}
