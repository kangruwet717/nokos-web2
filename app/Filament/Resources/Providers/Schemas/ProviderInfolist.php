<?php

namespace App\Filament\Resources\Providers\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class ProviderInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('code'),
                TextEntry::make('name'),
                TextEntry::make('base_url')->placeholder('-'),
                IconEntry::make('is_active')->boolean(),
                TextEntry::make('last_balance')->money('USD')->placeholder('-'),
                TextEntry::make('balance_checked_at')->dateTime()->placeholder('-'),
                TextEntry::make('created_at')->dateTime(),
            ]);
    }
}
