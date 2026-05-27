<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SupportTicket extends Model
{
    protected $fillable = [
        'ticket_no',
        'user_id',
        'otp_order_id',
        'payment_invoice_id',
        'category',
        'subject',
        'status',
        'priority',
        'last_replied_at',
        'closed_at',
    ];

    protected function casts(): array
    {
        return [
            'last_replied_at' => 'datetime',
            'closed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function otpOrder(): BelongsTo
    {
        return $this->belongsTo(OtpOrder::class);
    }

    public function paymentInvoice(): BelongsTo
    {
        return $this->belongsTo(PaymentInvoice::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(SupportTicketMessage::class);
    }

    public function isClosed(): bool
    {
        return $this->status === 'closed';
    }
}
