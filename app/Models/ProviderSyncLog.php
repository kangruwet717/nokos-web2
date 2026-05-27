<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProviderSyncLog extends Model
{
    protected $fillable = [
        'provider_id',
        'created_by_user_id',
        'sync_type',
        'country_code',
        'service_code',
        'status',
        'total_items',
        'processed_items',
        'failed_items',
        'started_at',
        'finished_at',
        'duration_ms',
        'error_message',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'total_items' => 'integer',
            'processed_items' => 'integer',
            'failed_items' => 'integer',
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
            'duration_ms' => 'integer',
            'meta' => 'array',
        ];
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }
}
