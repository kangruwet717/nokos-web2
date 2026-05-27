<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PaymentInvoice extends Model
{
    protected $fillable = [
        'invoice_no',
        'user_id',
        'provider',
        'external_id',
        'idempotency_key',
        'amount',
        'fee',
        'net_amount',
        'status',
        'payment_method',
        'payment_url',
        'raw_create_response',
        'expired_at',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'fee' => 'decimal:2',
            'net_amount' => 'decimal:2',
            'raw_create_response' => 'array',
            'expired_at' => 'datetime',
            'paid_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function webhookEvents(): HasMany
    {
        return $this->hasMany(PaymentWebhookEvent::class, 'invoice_id');
    }

    public function isFinal(): bool
    {
        return in_array($this->status, ['paid', 'failed', 'cancelled', 'expired'], true);
    }

    public function checkoutUrl(): ?string
    {
        return $this->payment_url
            ?? data_get($this->raw_create_response, 'payment_url')
            ?? data_get($this->raw_create_response, 'payment_link')
            ?? data_get($this->raw_create_response, 'checkout_url')
            ?? data_get($this->raw_create_response, 'checkoutUrl')
            ?? data_get($this->raw_create_response, 'url');
    }

    public function paymentCode(): ?string
    {
        return data_get($this->raw_create_response, 'payment_code')
            ?? data_get($this->raw_create_response, 'paymentCode')
            ?? data_get($this->raw_create_response, 'va_number')
            ?? data_get($this->raw_create_response, 'vaNumber')
            ?? data_get($this->raw_create_response, 'virtual_account')
            ?? data_get($this->raw_create_response, 'virtualAccount')
            ?? data_get($this->raw_create_response, 'qr_string')
            ?? data_get($this->raw_create_response, 'qrString');
    }

    public function qrImage(): ?string
    {
        return data_get($this->raw_create_response, 'qr_image')
            ?? data_get($this->raw_create_response, 'qrImage')
            ?? data_get($this->raw_create_response, 'qr_url')
            ?? data_get($this->raw_create_response, 'qrUrl')
            ?? data_get($this->raw_create_response, 'qr_code_url')
            ?? data_get($this->raw_create_response, 'qrCodeUrl');
    }
}
