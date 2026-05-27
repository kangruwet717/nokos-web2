<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProviderSyncScope extends Model
{
    protected $fillable = [
        'provider_id',
        'scope_key',
        'country_code',
        'service_code',
        'status',
        'last_queued_at',
        'last_synced_at',
        'last_success_at',
        'last_failed_at',
        'error_message',
    ];

    protected function casts(): array
    {
        return [
            'last_queued_at' => 'datetime',
            'last_synced_at' => 'datetime',
            'last_success_at' => 'datetime',
            'last_failed_at' => 'datetime',
        ];
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }
}
