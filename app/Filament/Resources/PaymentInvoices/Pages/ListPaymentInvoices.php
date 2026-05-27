<?php

namespace App\Filament\Resources\PaymentInvoices\Pages;

use App\Filament\Resources\PaymentInvoices\PaymentInvoiceResource;
use Filament\Resources\Pages\ListRecords;

class ListPaymentInvoices extends ListRecords
{
    protected static string $resource = PaymentInvoiceResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
