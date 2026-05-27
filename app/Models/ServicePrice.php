<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServicePrice extends Model
{
    protected $fillable = [
        'provider_id',
        'otp_service_id',
        'country_id',
        'provider_price_key',
        'provider_price',
        'provider_meta',
        'margin_type',
        'margin_value',
        'selling_price',
        'stock_count',
        'is_active',
        'last_synced_at',
    ];

    protected function casts(): array
    {
        return [
            'provider_price' => 'decimal:4',
            'provider_meta' => 'array',
            'margin_value' => 'decimal:2',
            'selling_price' => 'decimal:2',
            'stock_count' => 'integer',
            'is_active' => 'boolean',
            'last_synced_at' => 'datetime',
        ];
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }

    public function otpService(): BelongsTo
    {
        return $this->belongsTo(OtpService::class);
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }
}
