<?php

namespace App\Filament\Resources\Countries\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class CountryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('provider_code')->disabled(),
                TextInput::make('iso_code')->maxLength(2),
                TextInput::make('name')->required(),
                Toggle::make('is_active'),
                Toggle::make('is_blacklisted'),
            ]);
    }
}
