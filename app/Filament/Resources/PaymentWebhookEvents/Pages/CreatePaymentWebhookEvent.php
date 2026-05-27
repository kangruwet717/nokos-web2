<?php

namespace App\Filament\Resources\PaymentWebhookEvents\Pages;

use App\Filament\Resources\PaymentWebhookEvents\PaymentWebhookEventResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePaymentWebhookEvent extends CreateRecord
{
    protected static string $resource = PaymentWebhookEventResource::class;
}
