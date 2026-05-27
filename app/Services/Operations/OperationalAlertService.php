<?php

namespace App\Services\Operations;

use App\Models\OperationalAlert;
use App\Models\PaymentInvoice;
use App\Models\PaymentWebhookEvent;
use App\Models\Provider;
use App\Models\ProviderWebhookEvent;
use App\Models\User;

class OperationalAlertService
{
    public function check(): array
    {
        return [
            'provider_low_balance' => $this->checkProviderLowBalance(),
            'pending_invoice_overdue' => $this->checkPendingInvoiceOverdue(),
            'payment_webhook_errors' => $this->checkPaymentWebhookErrors(),
            'provider_webhook_errors' => $this->checkProviderWebhookErrors(),
        ];
    }

    public function openOrUpdate(string $type, string $dedupeKey, string $title, string $message, array $metadata = [], string $severity = 'warning'): OperationalAlert
    {
        return OperationalAlert::updateOrCreate(
            ['dedupe_key' => $dedupeKey],
            [
                'type' => $type,
                'severity' => $severity,
                'title' => $title,
                'message' => $message,
                'metadata' => $metadata ?: null,
                'resolved_at' => null,
                'resolved_by_user_id' => null,
            ],
        );
    }

    public function resolve(OperationalAlert $alert, ?User $user = null): OperationalAlert
    {
        if ($alert->resolved_at) {
            return $alert;
        }

        $alert->forceFill([
            'resolved_at' => now(),
            'resolved_by_user_id' => $user?->id,
        ])->save();

        return $alert->refresh();
    }

    private function checkProviderLowBalance(): int
    {
        $created = 0;
        $threshold = (float) config('operations.provider_low_balance_threshold', 5);

        Provider::query()
            ->where('is_active', true)
            ->whereNotNull('last_balance')
            ->where('last_balance', '<=', $threshold)
            ->get()
            ->each(function (Provider $provider) use (&$created, $threshold): void {
                $this->openOrUpdate(
                    'provider_low_balance',
                    "provider_low_balance:{$provider->id}",
                    "{$provider->name} balance rendah",
                    "Saldo provider {$provider->name} {$provider->last_balance}, di bawah threshold {$threshold}.",
                    [
                        'provider_id' => $provider->id,
                        'provider_code' => $provider->code,
                        'last_balance' => $provider->last_balance,
                        'threshold' => $threshold,
                    ],
                    'critical',
                );
                $created++;
            });

        return $created;
    }

    private function checkPendingInvoiceOverdue(): int
    {
        $minutes = (int) config('operations.pending_invoice_overdue_minutes', 30);
        $count = PaymentInvoice::query()
            ->where('status', 'pending')
            ->whereNotNull('expired_at')
            ->where('expired_at', '<', now()->subMinutes($minutes))
            ->count();

        if ($count < 1) {
            return 0;
        }

        $this->openOrUpdate(
            'pending_invoice_overdue',
            'pending_invoice_overdue',
            'Invoice pending melewati expiry',
            "{$count} invoice pending sudah melewati expired_at lebih dari {$minutes} menit.",
            ['count' => $count, 'overdue_minutes' => $minutes],
            'warning',
        );

        return $count;
    }

    private function checkPaymentWebhookErrors(): int
    {
        $minutes = (int) config('operations.webhook_error_window_minutes', 60);
        $threshold = (int) config('operations.webhook_error_threshold', 3);
        $count = PaymentWebhookEvent::query()
            ->where('created_at', '>=', now()->subMinutes($minutes))
            ->where(function ($query): void {
                $query->where('signature_valid', false)
                    ->orWhereNotNull('error_message');
            })
            ->count();

        if ($count < $threshold) {
            return 0;
        }

        $this->openOrUpdate(
            'payment_webhook_errors',
            'payment_webhook_errors',
            'Payment webhook error meningkat',
            "{$count} payment webhook bermasalah dalam {$minutes} menit terakhir.",
            ['count' => $count, 'window_minutes' => $minutes, 'threshold' => $threshold],
            'critical',
        );

        return $count;
    }

    private function checkProviderWebhookErrors(): int
    {
        $minutes = (int) config('operations.webhook_error_window_minutes', 60);
        $threshold = (int) config('operations.webhook_error_threshold', 3);
        $count = ProviderWebhookEvent::query()
            ->where('created_at', '>=', now()->subMinutes($minutes))
            ->where(function ($query): void {
                $query->where('signature_valid', false)
                    ->orWhereNotNull('error_message');
            })
            ->count();

        if ($count < $threshold) {
            return 0;
        }

        $this->openOrUpdate(
            'provider_webhook_errors',
            'provider_webhook_errors',
            'Provider webhook error meningkat',
            "{$count} provider webhook bermasalah dalam {$minutes} menit terakhir.",
            ['count' => $count, 'window_minutes' => $minutes, 'threshold' => $threshold],
            'critical',
        );

        return $count;
    }
}
