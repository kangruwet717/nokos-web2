<?php

namespace Tests\Unit;

use App\Jobs\SyncSmsbowerCatalogJob;
use App\Services\Providers\SmsbowerCatalogSyncService;
use App\Services\Providers\ProviderSyncTracker;
use App\Support\ProviderSyncStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use RuntimeException;
use Tests\TestCase;

class SyncSmsbowerCatalogJobTest extends TestCase
{
    use RefreshDatabase;

    public function test_job_marks_sync_as_completed(): void
    {
        $sync = Mockery::mock(SmsbowerCatalogSyncService::class);
        $sync->shouldReceive('sync')->once()->andReturn([
            'services' => 2,
            'countries' => 3,
            'prices' => 4,
            'balance' => '1.84',
        ]);

        (new SyncSmsbowerCatalogJob)->handle($sync, app(ProviderSyncTracker::class));

        $status = ProviderSyncStatus::current();

        $this->assertSame('completed', $status['status']);
        $this->assertSame('Completed', $status['label']);
        $this->assertStringContainsString('prices 4', $status['message']);
        $this->assertDatabaseHas('provider_sync_logs', [
            'sync_type' => 'full',
            'status' => 'success',
            'processed_items' => 9,
        ]);
        $this->assertDatabaseHas('provider_sync_scopes', [
            'scope_key' => 'full',
            'status' => 'success',
        ]);
    }

    public function test_job_marks_sync_as_failed(): void
    {
        $sync = Mockery::mock(SmsbowerCatalogSyncService::class);
        $sync->shouldReceive('sync')->once()->andThrow(new RuntimeException('Provider timeout'));

        try {
            (new SyncSmsbowerCatalogJob)->handle($sync, app(ProviderSyncTracker::class));
        } catch (RuntimeException) {
            //
        }

        $status = ProviderSyncStatus::current();

        $this->assertSame('failed', $status['status']);
        $this->assertSame('Provider timeout', $status['message']);
        $this->assertDatabaseHas('provider_sync_logs', [
            'sync_type' => 'full',
            'status' => 'failed',
            'error_message' => 'Provider timeout',
        ]);
    }
}
