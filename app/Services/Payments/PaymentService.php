<?php

namespace App\Services\Payments;

use App\Models\PaymentInvoice;
use App\Models\PaymentWebhookEvent;
use App\Models\User;
use App\Services\Audit\AuditLogService;
use App\Services\Wallet\WalletService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class PaymentService
{
    public function __construct(
        private readonly PaymentGatewayInterface $gateway,
        private readonly WalletService $wallet,
        private readonly AuditLogService $audit,
    ) {}

    public function createTopUpInvoice(User $user, string $amount): PaymentInvoice
    {
        $this->assertTopUpAmount($amount);

        $invoice = PaymentInvoice::create([
            'invoice_no' => $this->generateInvoiceNo(),
            'user_id' => $user->id,
            'provider' => 'dompetx',
            'idempotency_key' => (string) Str::uuid(),
            'amount' => $amount,
            'net_amount' => $amount,
            'status' => 'pending',
        ]);

        try {
            $response = $this->gateway->createCheckout([
                'amount' => (int) $amount,
                'currency' => 'IDR',
                'reference' => $invoice->invoice_no,
                'metadata' => [
                    'user_id' => $user->id,
                    'invoice_id' => $invoice->id,
                    'type' => 'wallet_topup',
                ],
            ], $invoice->idempotency_key);
        } catch (\Throwable $exception) {
            $invoice->forceFill(['status' => 'failed'])->save();
            throw $exception;
        }

        $invoice->forceFill([
            'external_id' => $response['id'] ?? null,
            'status' => $this->normalizeStatus($response['status'] ?? 'pending'),
            'payment_url' => $this->paymentUrlFromResponse($response),
            'raw_create_response' => $response,
            'expired_at' => isset($response['expiresAt']) ? now()->parse($response['expiresAt']) : null,
        ])->save();

        $this->audit->record('payment.invoice_created', $user, $invoice, [
            'amount' => $amount,
            'external_id' => $invoice->external_id,
        ]);

        return $invoice;
    }

    public function processWebhook(PaymentWebhookEvent $event): void
    {
        DB::transaction(function () use ($event) {
            /** @var PaymentWebhookEvent $lockedEvent */
            $lockedEvent = PaymentWebhookEvent::query()->whereKey($event->id)->lockForUpdate()->firstOrFail();

            if ($lockedEvent->processed) {
                return;
            }

            $payload = $lockedEvent->payload;
            $data = $payload['data'] ?? [];
            $reference = $data['reference'] ?? null;
            $externalId = $payload['paymentId'] ?? $data['id'] ?? null;
            $status = $this->normalizeStatus($data['status'] ?? null);

            /** @var PaymentInvoice|null $invoice */
            $invoice = PaymentInvoice::query()
                ->where('invoice_no', $reference)
                ->orWhere('external_id', $externalId)
                ->lockForUpdate()
                ->first();

            if (! $invoice) {
                $lockedEvent->forceFill([
                    'error_message' => 'Invoice not found for DompetX webhook.',
                    'processed' => true,
                    'processed_at' => now(),
                ])->save();

                return;
            }

            $lockedEvent->forceFill([
                'invoice_id' => $invoice->id,
                'external_id' => $externalId,
            ])->save();

            if ($invoice->status === 'paid') {
                $lockedEvent->forceFill(['processed' => true, 'processed_at' => now()])->save();

                return;
            }

            if ($status === 'paid') {
                $this->markInvoicePaid($invoice, $data, $lockedEvent);
            } elseif (in_array($status, ['failed', 'cancelled', 'expired'], true) && $invoice->status === 'pending') {
                $invoice->forceFill(['status' => $status])->save();
            }

            $lockedEvent->forceFill(['processed' => true, 'processed_at' => now()])->save();
        });
    }

    public function reconcile(PaymentInvoice $invoice): PaymentInvoice
    {
        if (! $invoice->external_id || $invoice->status === 'paid') {
            return $invoice;
        }

        $response = $this->gateway->checkCheckoutStatus($invoice->external_id);
        $status = $this->normalizeStatus($response['status'] ?? null);

        DB::transaction(function () use ($invoice, $response, $status) {
            /** @var PaymentInvoice $lockedInvoice */
            $lockedInvoice = PaymentInvoice::query()->whereKey($invoice->id)->lockForUpdate()->firstOrFail();

            if ($lockedInvoice->status === 'paid') {
                return;
            }

            if ($status === 'paid') {
                $this->markInvoicePaid($lockedInvoice, $response);
            } elseif (in_array($status, ['failed', 'cancelled', 'expired'], true) && $lockedInvoice->status === 'pending') {
                $lockedInvoice->forceFill([
                    'status' => $status,
                    'fee' => $response['fee'] ?? $lockedInvoice->fee,
                    'payment_method' => $response['type'] ?? $lockedInvoice->payment_method,
                ])->save();
            }
        });

        return $invoice->refresh();
    }

    public function reconcilePending(int $limit = 50): array
    {
        $stats = [
            'checked' => 0,
            'paid' => 0,
            'expired' => 0,
            'failed' => 0,
            'errors' => 0,
        ];

        PaymentInvoice::query()
            ->where('status', 'pending')
            ->orderBy('created_at')
            ->limit($limit)
            ->get()
            ->each(function (PaymentInvoice $invoice) use (&$stats) {
                $stats['checked']++;
                $previousStatus = $invoice->status;

                try {
                    if ($invoice->external_id) {
                        $invoice = $this->reconcile($invoice);
                    }

                    if ($invoice->status === 'pending' && $this->isPastExpiryGrace($invoice)) {
                        $invoice = $this->expireInvoice($invoice);
                    }

                    if ($invoice->status === 'paid' && $previousStatus !== 'paid') {
                        $stats['paid']++;
                    } elseif ($invoice->status === 'expired' && $previousStatus !== 'expired') {
                        $stats['expired']++;
                    } elseif (in_array($invoice->status, ['failed', 'cancelled'], true) && $previousStatus !== $invoice->status) {
                        $stats['failed']++;
                    }
                } catch (\Throwable $exception) {
                    $stats['errors']++;
                    report($exception);
                }
            });

        return $stats;
    }

    public function expireInvoice(PaymentInvoice $invoice): PaymentInvoice
    {
        DB::transaction(function () use ($invoice) {
            /** @var PaymentInvoice $lockedInvoice */
            $lockedInvoice = PaymentInvoice::query()->whereKey($invoice->id)->lockForUpdate()->firstOrFail();

            if ($lockedInvoice->status !== 'pending') {
                return;
            }

            $lockedInvoice->forceFill(['status' => 'expired'])->save();

            $this->audit->record('payment.invoice_expired', $lockedInvoice->user, $lockedInvoice, [
                'amount' => $lockedInvoice->amount,
                'external_id' => $lockedInvoice->external_id,
            ]);
        });

        return $invoice->refresh();
    }

    private function markInvoicePaid(PaymentInvoice $invoice, array $data, ?PaymentWebhookEvent $event = null): void
    {
        $amount = (string) ($data['amount'] ?? $invoice->amount);

        if (bccomp($amount, (string) $invoice->amount, 2) !== 0) {
            throw new RuntimeException('DompetX paid amount does not match invoice amount.');
        }

        $invoice->forceFill([
            'status' => 'paid',
            'external_id' => $invoice->external_id ?: ($data['id'] ?? $event?->external_id),
            'fee' => $data['fee'] ?? $invoice->fee,
            'payment_method' => $data['type'] ?? $invoice->payment_method,
            'paid_at' => now(),
        ])->save();

        $this->wallet->credit(
            $invoice->user,
            (string) $invoice->net_amount,
            'topup',
            $invoice,
            "Top up {$invoice->invoice_no}",
            ['provider' => 'dompetx', 'external_id' => $invoice->external_id],
        );

        $this->audit->record('payment.invoice_paid', $invoice->user, $invoice, [
            'amount' => $invoice->net_amount,
            'external_id' => $invoice->external_id,
        ]);
    }

    private function assertTopUpAmount(string $amount): void
    {
        if (! is_numeric($amount) || bccomp($amount, '10000', 2) < 0 || bccomp($amount, '10000000', 2) > 0) {
            throw new RuntimeException('Top up amount must be between Rp10.000 and Rp10.000.000.');
        }
    }

    private function normalizeStatus(?string $status): string
    {
        return match (strtolower((string) $status)) {
            'paid', 'success', 'settled' => 'paid',
            'failed', 'failure' => 'failed',
            'cancelled', 'canceled' => 'cancelled',
            'expired' => 'expired',
            default => 'pending',
        };
    }

    private function paymentUrlFromResponse(array $response): ?string
    {
        return $response['payment_url']
            ?? $response['payment_link']
            ?? $response['checkout_url']
            ?? $response['checkoutUrl']
            ?? $response['url']
            ?? null;
    }

    private function generateInvoiceNo(): string
    {
        do {
            $invoiceNo = 'TOPUP-'.now()->format('YmdHis').'-'.Str::upper(Str::random(6));
        } while (PaymentInvoice::where('invoice_no', $invoiceNo)->exists());

        return $invoiceNo;
    }

    private function isPastExpiryGrace(PaymentInvoice $invoice): bool
    {
        if (! $invoice->expired_at) {
            return false;
        }

        $graceMinutes = (int) env('PAYMENT_EXPIRE_GRACE_MINUTES', 10);

        return $invoice->expired_at->addMinutes($graceMinutes)->isPast();
    }
}
