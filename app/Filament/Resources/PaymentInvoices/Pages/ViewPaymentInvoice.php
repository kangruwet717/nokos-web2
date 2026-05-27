<?php

namespace App\Filament\Resources\PaymentInvoices\Pages;

use App\Filament\Resources\PaymentInvoices\PaymentInvoiceResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewPaymentInvoice extends ViewRecord
{
    protected static string $resource = PaymentInvoiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
