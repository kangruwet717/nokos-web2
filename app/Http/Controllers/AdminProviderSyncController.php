<?php

namespace App\Http\Controllers;

use App\Jobs\SyncSmsbowerCatalogJob;
use App\Models\Provider;
use App\Services\Providers\ProviderSyncTracker;
use App\Support\ProviderSyncStatus;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AdminProviderSyncController extends Controller
{
    public function queue(Request $request, Provider $provider, ProviderSyncTracker $tracker): RedirectResponse
    {
        if ($provider->code !== 'smsbower') {
            abort(404);
        }

        $log = $tracker->markQueued(createdByUserId: $request->user()?->id);
        ProviderSyncStatus::markQueued();
        SyncSmsbowerCatalogJob::dispatch(syncLogId: $log->id);

        return redirect()
            ->route('filament.admin.resources.providers.index')
            ->with('status', 'SMSBower sync queued. Jalankan queue worker jika status tidak berubah.');
    }
}
