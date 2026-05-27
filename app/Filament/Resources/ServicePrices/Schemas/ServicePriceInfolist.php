<?php

namespace App\Filament\Resources\ServicePrices\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class ServicePriceInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('provider.name'),
                TextEntry::make('otpService.name')->label('Service'),
                TextEntry::make('country.name'),
                TextEntry::make('provider_price_key')->label('Variant'),
                TextEntry::make('provider_meta')->json()->placeholder('-'),
                TextEntry::make('provider_price')->money('USD'),
                TextEntry::make('selling_price')->money('IDR'),
                TextEntry::make('stock_count'),
                TextEntry::make('margin_type')->badge(),
                TextEntry::make('margin_value'),
                IconEntry::make('is_active')->boolean(),
                TextEntry::make('last_synced_at')->dateTime()->placeholder('-'),
            ]);
    }
}
