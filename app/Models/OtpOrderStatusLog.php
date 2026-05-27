<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OtpOrderStatusLog extends Model
{
    protected $fillable = [
        'otp_order_id',
        'old_status',
        'new_status',
        'source',
        'message',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
        ];
    }

    public function otpOrder(): BelongsTo
    {
        return $this->belongsTo(OtpOrder::class);
    }
}
