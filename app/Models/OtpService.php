<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OtpService extends Model
{
    protected $fillable = [
        'provider_id',
        'provider_code',
        'name',
        'category',
        'icon_url',
        'is_active',
        'is_blacklisted',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'is_blacklisted' => 'boolean',
        ];
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }

    public function prices(): HasMany
    {
        return $this->hasMany(ServicePrice::class);
    }
}
