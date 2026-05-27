<?php

namespace App\Filament\Resources\ProviderWebhookEvents\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class ProviderWebhookEventInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('provider')->badge(),
                TextEntry::make('event_id')->placeholder('-'),
                TextEntry::make('activation_id')->placeholder('-'),
                TextEntry::make('otpOrder.order_no')->placeholder('-'),
                IconEntry::make('signature_valid')->boolean(),
                IconEntry::make('processed')->boolean(),
                TextEntry::make('processed_at')->dateTime()->placeholder('-'),
                TextEntry::make('error_message')->placeholder('-'),
                TextEntry::make('created_at')->dateTime(),
            ]);
    }
}
