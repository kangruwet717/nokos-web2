<?php

namespace App\Filament\Resources\PaymentWebhookEvents\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class PaymentWebhookEventInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('provider'),
                TextEntry::make('event_id')->placeholder('-'),
                TextEntry::make('external_id')->placeholder('-'),
                TextEntry::make('invoice.invoice_no')->placeholder('-'),
                TextEntry::make('event_type')->placeholder('-'),
                IconEntry::make('signature_valid')->boolean(),
                IconEntry::make('processed')->boolean(),
                TextEntry::make('processed_at')->dateTime()->placeholder('-'),
                TextEntry::make('error_message')->placeholder('-'),
                TextEntry::make('created_at')->dateTime(),
            ]);
    }
}
