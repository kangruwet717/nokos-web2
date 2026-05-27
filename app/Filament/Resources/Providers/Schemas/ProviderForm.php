<?php

namespace App\Filament\Resources\Providers\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ProviderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('code')->required()->disabled(),
                TextInput::make('name')->required(),
                TextInput::make('base_url')->url(),
                Toggle::make('is_active'),
            ]);
    }
}
