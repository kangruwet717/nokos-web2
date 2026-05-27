<?php

namespace App\Console\Commands;

use App\Jobs\SyncSmsbowerCatalogJob;
use App\Services\Providers\SmsbowerCatalogSyncService;
use App\Services\Providers\ProviderSyncTracker;
use App\Support\ProviderSyncStatus;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class SyncSmsbowerCatalogCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'smsbower:sync-catalog
        {--service= : Sync prices only for one SMSBower service code, for example wa}
        {--country= : Sync prices only for one SMSBower country code, for example 6}
        {--queue : Push the sync to the queue instead of running it immediately}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync SMSBower services, countries, prices, and provider balance';

    /**
     * Execute the console command.
     */
    public function handle(SmsbowerCatalogSyncService $sync, ProviderSyncTracker $tracker): int
    {
        $serviceCode = $this->option('service') ? (string) $this->option('service') : null;
        $countryCode = $this->option('country') ? (string) $this->option('country') : null;
        $log = $tracker->markQueued($serviceCode, $countryCode);

        if ((bool) $this->option('queue')) {
            ProviderSyncStatus::markQueued($tracker->scopeLabel($serviceCode, $countryCode));
            SyncSmsbowerCatalogJob::dispatch($serviceCode, $countryCode, $log->id);

            $this->components->info('SMSBower sync queued.');

            return self::SUCCESS;
        }

        $lock = Cache::lock($tracker->lockKey($serviceCode, $countryCode), 1800);

        if (! $lock->get()) {
            $tracker->markSkipped($log, 'Sync scope yang sama masih berjalan.');
            $this->components->warn('SMSBower sync skipped because the same scope is already running.');

            return self::SUCCESS;
        }

        try {
            ProviderSyncStatus::markRunning($tracker->scopeLabel($serviceCode, $countryCode));
            $tracker->markRunning($log);

            $result = $sync->sync($serviceCode, $countryCode);
        } catch (\Throwable $exception) {
            report($exception);
            ProviderSyncStatus::markFailed($exception->getMessage());
            $tracker->markFailed($log, $exception->getMessage());
            $this->components->error($exception->getMessage());

            return self::FAILURE;
        } finally {
            $lock->release();
        }

        $scope = $serviceCode || $countryCode
            ? ' scoped to '.trim("service {$serviceCode} country {$countryCode}")
            : '';

        ProviderSyncStatus::markCompleted($result);
        $tracker->markSucceeded($log, $result);
        $this->components->info("SMSBower synced{$scope}: services {$result['services']}, countries {$result['countries']}, prices {$result['prices']}, balance {$result['balance']}.");

        return self::SUCCESS;
    }
}
