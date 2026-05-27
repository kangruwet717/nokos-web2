<?php

namespace App\Filament\Resources\Countries\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class CountryInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('provider.name'),
                TextEntry::make('provider_code'),
                TextEntry::make('iso_code')->placeholder('-'),
                TextEntry::make('name'),
                IconEntry::make('is_active')->boolean(),
                IconEntry::make('is_blacklisted')->boolean(),
                TextEntry::make('created_at')->dateTime(),
            ]);
    }
}
