<?php

namespace App\Filament\Resources\PaymentInvoices\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class PaymentInvoiceInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('invoice_no'),
                TextEntry::make('user.email'),
                TextEntry::make('provider'),
                TextEntry::make('external_id')->placeholder('-'),
                TextEntry::make('idempotency_key'),
                TextEntry::make('amount')->money('IDR'),
                TextEntry::make('fee')->money('IDR'),
                TextEntry::make('net_amount')->money('IDR'),
                TextEntry::make('status')->badge(),
                TextEntry::make('payment_method')->placeholder('-'),
                TextEntry::make('payment_url')->placeholder('-'),
                TextEntry::make('expired_at')->dateTime()->placeholder('-'),
                TextEntry::make('paid_at')->dateTime()->placeholder('-'),
                TextEntry::make('created_at')->dateTime(),
            ]);
    }
}
