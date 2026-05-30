<?php

namespace App\Jobs;

use App\Models\ProviderSyncLog;
use App\Services\Providers\SmsbowerCatalogSyncService;
use App\Services\Providers\ProviderSyncTracker;
use App\Support\ProviderSyncStatus;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Cache;

class SyncSmsbowerCatalogJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 1;

    public int $timeout = 1800;

    public function __construct(
        public readonly ?string $serviceCode = null,
        public readonly ?string $countryCode = null,
        public readonly ?int $syncLogId = null,
    ) {}

    public function handle(SmsbowerCatalogSyncService $sync, ProviderSyncTracker $tracker): void
    {
        $log = $this->syncLogId
            ? ProviderSyncLog::query()->find($this->syncLogId)
            : null;
        $log ??= $tracker->markQueued($this->serviceCode, $this->countryCode);
        $lock = Cache::lock($tracker->lockKey($this->serviceCode, $this->countryCode), 3600);

        if (! $lock->get()) {
            $tracker->markSkipped($log, 'Sync scope yang sama masih berjalan.');

            return;
        }

        try {
            ProviderSyncStatus::markRunning($tracker->scopeLabel($this->serviceCode, $this->countryCode));
            $tracker->markRunning($log);

            $result = $sync->sync($this->serviceCode, $this->countryCode);

            ProviderSyncStatus::markCompleted($result);
            $tracker->markSucceeded($log, $result);
        } catch (\Throwable $exception) {
            ProviderSyncStatus::markFailed($exception->getMessage());
            $tracker->markFailed($log, $exception->getMessage());

            throw $exception;
        } finally {
            $lock->release();
        }
    }

    public function failed(\Throwable $exception): void
    {
        ProviderSyncStatus::markFailed($exception->getMessage());
    }

}
