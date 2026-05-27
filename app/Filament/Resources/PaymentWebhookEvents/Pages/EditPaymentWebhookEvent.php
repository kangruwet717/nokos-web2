<?php

namespace App\Filament\Resources\PaymentWebhookEvents\Pages;

use App\Filament\Resources\PaymentWebhookEvents\PaymentWebhookEventResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditPaymentWebhookEvent extends EditRecord
{
    protected static string $resource = PaymentWebhookEventResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
