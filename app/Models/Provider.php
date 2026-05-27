<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Provider extends Model
{
    protected $fillable = [
        'code',
        'name',
        'base_url',
        'is_active',
        'config_json',
        'last_balance',
        'balance_checked_at',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'config_json' => 'encrypted:array',
            'last_balance' => 'decimal:2',
            'balance_checked_at' => 'datetime',
        ];
    }

    public function otpServices(): HasMany
    {
        return $this->hasMany(OtpService::class);
    }

    public function countries(): HasMany
    {
        return $this->hasMany(Country::class);
    }

    public function servicePrices(): HasMany
    {
        return $this->hasMany(ServicePrice::class);
    }

    public function syncLogs(): HasMany
    {
        return $this->hasMany(ProviderSyncLog::class);
    }

    public function syncScopes(): HasMany
    {
        return $this->hasMany(ProviderSyncScope::class);
    }
}
