<?php

namespace App\Filament\Resources\ServicePrices\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ServicePriceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('provider_price_key')->disabled(),
                TextInput::make('provider_price')->numeric()->disabled(),
                Select::make('margin_type')
                    ->options(['fixed' => 'Fixed', 'percent' => 'Percent'])
                    ->required(),
                TextInput::make('margin_value')->numeric()->required(),
                TextInput::make('selling_price')->numeric()->required(),
                TextInput::make('stock_count')->numeric()->disabled(),
                Toggle::make('is_active'),
            ]);
    }
}
