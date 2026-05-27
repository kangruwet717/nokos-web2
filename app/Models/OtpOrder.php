<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OtpOrder extends Model
{
    protected $fillable = [
        'order_no',
        'user_id',
        'provider_id',
        'otp_service_id',
        'country_id',
        'provider_activation_id',
        'phone_number',
        'phone_number_masked',
        'provider_cost',
        'selling_price',
        'margin_amount',
        'status',
        'sms_code',
        'sms_text_masked',
        'raw_provider_response',
        'expires_at',
        'completed_at',
        'cancelled_at',
        'refunded_at',
        'refund_reason',
    ];

    protected function casts(): array
    {
        return [
            'provider_cost' => 'decimal:4',
            'selling_price' => 'decimal:2',
            'margin_amount' => 'decimal:2',
            'sms_code' => 'encrypted',
            'raw_provider_response' => 'array',
            'expires_at' => 'datetime',
            'completed_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'refunded_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
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

    public function statusLogs(): HasMany
    {
        return $this->hasMany(OtpOrderStatusLog::class);
    }

    public function providerWebhookEvents(): HasMany
    {
        return $this->hasMany(ProviderWebhookEvent::class);
    }

    public function canBeCancelled(): bool
    {
        return $this->status === 'waiting_sms' && blank($this->sms_code);
    }
}
