<?php

namespace App\Filament\Resources\PaymentWebhookEvents\Pages;

use App\Filament\Resources\PaymentWebhookEvents\PaymentWebhookEventResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewPaymentWebhookEvent extends ViewRecord
{
    protected static string $resource = PaymentWebhookEventResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
