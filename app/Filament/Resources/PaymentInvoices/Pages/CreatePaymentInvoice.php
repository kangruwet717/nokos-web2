<?php

namespace App\Filament\Resources\PaymentInvoices\Pages;

use App\Filament\Resources\PaymentInvoices\PaymentInvoiceResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePaymentInvoice extends CreateRecord
{
    protected static string $resource = PaymentInvoiceResource::class;
}
