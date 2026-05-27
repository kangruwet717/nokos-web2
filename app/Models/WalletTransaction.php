<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class WalletTransaction extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'direction',
        'amount',
        'balance_before',
        'balance_after',
        'reserved_before',
        'reserved_after',
        'reference_type',
        'reference_id',
        'status',
        'description',
        'metadata',
        'created_by_admin_id',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'balance_before' => 'decimal:2',
            'balance_after' => 'decimal:2',
            'reserved_before' => 'decimal:2',
            'reserved_after' => 'decimal:2',
            'metadata' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_admin_id');
    }

    public function reference(): MorphTo
    {
        return $this->morphTo();
    }
}
