<?php

namespace App\Services\Providers;

use App\Models\Provider;
use App\Models\ProviderSyncLog;
use App\Models\ProviderSyncScope;
use Illuminate\Support\Carbon;

class ProviderSyncTracker
{
    public function provider(string $code = 'smsbower'): Provider
    {
        return Provider::query()->firstOrCreate(
            ['code' => $code],
            ['name' => strtoupper($code), 'is_active' => true],
        );
    }

    public function scopeKey(?string $serviceCode = null, ?string $countryCode = null): string
    {
        if (! $serviceCode && ! $countryCode) {
            return 'full';
        }

        return 'country:'.($countryCode ?: '*').':service:'.($serviceCode ?: '*');
    }

    public function lockKey(?string $serviceCode = null, ?string $countryCode = null): string
    {
        return 'smsbower:sync:'.$this->scopeKey($serviceCode, $countryCode);
    }

    public function scopeLabel(?string $serviceCode = null, ?string $countryCode = null): ?string
    {
        if (! $serviceCode && ! $countryCode) {
            return null;
        }

        return trim("Scope service {$serviceCode} country {$countryCode}.");
    }

    public function recentlyQueuedOrSynced(?string $serviceCode, ?string $countryCode, int $cooldownSeconds): ?ProviderSyncScope
    {
        $scope = $this->scope($serviceCode, $countryCode);
        $threshold = now()->subSeconds($cooldownSeconds);

        if (in_array($scope->status, ['queued', 'running'], true)) {
            return $scope;
        }

        if ($scope->last_success_at && $scope->last_success_at->greaterThan($threshold)) {
            return $scope;
        }

        if ($scope->last_queued_at && $scope->last_queued_at->greaterThan($threshold)) {
            return $scope;
        }

        return null;
    }

    public function markQueued(?string $serviceCode = null, ?string $countryCode = null, ?int $createdByUserId = null): ProviderSyncLog
    {
        $provider = $this->provider();
        $syncType = $serviceCode || $countryCode ? 'scoped' : 'full';

        $this->scope($serviceCode, $countryCode)->update([
            'status' => 'queued',
            'last_queued_at' => now(),
            'error_message' => null,
        ]);

        return ProviderSyncLog::query()->create([
            'provider_id' => $provider->id,
            'created_by_user_id' => $createdByUserId,
            'sync_type' => $syncType,
            'country_code' => $countryCode,
            'service_code' => $serviceCode,
            'status' => 'queued',
        ]);
    }

    public function markRunning(ProviderSyncLog $log): void
    {
        $startedAt = now();

        $log->update([
            'status' => 'running',
            'started_at' => $startedAt,
        ]);

        $this->scope($log->service_code, $log->country_code)->update([
            'status' => 'running',
            'last_synced_at' => $startedAt,
            'error_message' => null,
        ]);
    }

    public function markSucceeded(ProviderSyncLog $log, array $result): void
    {
        $finishedAt = now();
        $processedItems = (int) ($result['services'] ?? 0)
            + (int) ($result['countries'] ?? 0)
            + (int) ($result['prices'] ?? 0);

        $log->update([
            'status' => 'success',
            'total_items' => $processedItems,
            'processed_items' => $processedItems,
            'finished_at' => $finishedAt,
            'duration_ms' => $this->durationMs($log->started_at, $finishedAt),
            'meta' => $result,
        ]);

        $this->scope($log->service_code, $log->country_code)->update([
            'status' => 'success',
            'last_success_at' => $finishedAt,
            'error_message' => null,
        ]);
    }

    public function markFailed(ProviderSyncLog $log, string $message): void
    {
        $finishedAt = now();

        $log->update([
            'status' => 'failed',
            'failed_items' => max((int) $log->failed_items, 1),
            'finished_at' => $finishedAt,
            'duration_ms' => $this->durationMs($log->started_at, $finishedAt),
            'error_message' => $message,
        ]);

        $this->scope($log->service_code, $log->country_code)->update([
            'status' => 'failed',
            'last_failed_at' => $finishedAt,
            'error_message' => $message,
        ]);
    }

    public function markSkipped(ProviderSyncLog $log, string $message): void
    {
        $finishedAt = now();

        $log->update([
            'status' => 'skipped',
            'finished_at' => $finishedAt,
            'duration_ms' => $this->durationMs($log->started_at, $finishedAt),
            'error_message' => $message,
        ]);
    }

    public function scope(?string $serviceCode = null, ?string $countryCode = null): ProviderSyncScope
    {
        $provider = $this->provider();

        return ProviderSyncScope::query()->firstOrCreate(
            [
                'provider_id' => $provider->id,
                'scope_key' => $this->scopeKey($serviceCode, $countryCode),
            ],
            [
                'country_code' => $countryCode,
                'service_code' => $serviceCode,
                'status' => 'idle',
            ],
        );
    }

    private function durationMs(?Carbon $startedAt, Carbon $finishedAt): ?int
    {
        if (! $startedAt) {
            return null;
        }

        return (int) $startedAt->diffInMilliseconds($finishedAt, true);
    }
}
