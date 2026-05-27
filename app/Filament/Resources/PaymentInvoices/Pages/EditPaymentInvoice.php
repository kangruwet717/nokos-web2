<?php

namespace App\Filament\Resources\PaymentInvoices\Pages;

use App\Filament\Resources\PaymentInvoices\PaymentInvoiceResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditPaymentInvoice extends EditRecord
{
    protected static string $resource = PaymentInvoiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
