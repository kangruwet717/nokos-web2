<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProviderWebhookEvent extends Model
{
    protected $fillable = [
        'provider',
        'event_id',
        'activation_id',
        'otp_order_id',
        'payload',
        'signature_valid',
        'processed',
        'processed_at',
        'error_message',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'signature_valid' => 'boolean',
            'processed' => 'boolean',
            'processed_at' => 'datetime',
        ];
    }

    public function otpOrder(): BelongsTo
    {
        return $this->belongsTo(OtpOrder::class);
    }
}
