<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;

class ProviderSyncStatus
{
    private const KEY = 'providers.smsbower.sync_status';

    public static function current(): array
    {
        return Cache::get(self::KEY, [
            'status' => 'idle',
            'label' => 'Idle',
            'message' => 'Belum ada sync berjalan.',
            'updated_at' => null,
        ]);
    }

    public static function markQueued(?string $scope = null): void
    {
        self::put('queued', 'Queued', trim('Menunggu queue worker menjalankan sync. '.$scope));
    }

    public static function markRunning(?string $scope = null): void
    {
        self::put('running', 'Running', trim('Sync katalog SMSBower sedang berjalan. '.$scope));
    }

    public static function markCompleted(array $result): void
    {
        self::put(
            'completed',
            'Completed',
            "Services {$result['services']}, countries {$result['countries']}, prices {$result['prices']}, balance {$result['balance']}.",
        );
    }

    public static function markFailed(string $message): void
    {
        self::put('failed', 'Failed', $message);
    }

    public static function markStopped(): void
    {
        self::put('idle', 'Stopped', 'Auto-sync SMSBower dimatikan.');
    }

    public static function color(string $status): string
    {
        return match ($status) {
            'queued' => 'warning',
            'running' => 'info',
            'completed' => 'success',
            'failed' => 'danger',
            default => 'gray',
        };
    }

    private static function put(string $status, string $label, string $message): void
    {
        Cache::put(self::KEY, [
            'status' => $status,
            'label' => $label,
            'message' => $message,
            'updated_at' => now()->toDateTimeString(),
        ], now()->addHours(6));
    }
}
