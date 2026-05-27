<?php

namespace App\Filament\Resources\OtpServices\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class OtpServiceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('provider_code')->disabled(),
                TextInput::make('name')->required(),
                TextInput::make('category'),
                TextInput::make('icon_url')
                    ->label('Icon URL')
                    ->url()
                    ->maxLength(255)
                    ->helperText('Opsional. Dipakai untuk layanan yang belum punya logo otomatis.'),
                Toggle::make('is_active'),
                Toggle::make('is_blacklisted'),
            ]);
    }
}
