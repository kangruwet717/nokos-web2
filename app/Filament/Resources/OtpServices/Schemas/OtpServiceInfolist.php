<?php

namespace App\Filament\Resources\OtpServices\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class OtpServiceInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('provider.name'),
                TextEntry::make('provider_code'),
                TextEntry::make('name'),
                TextEntry::make('category')->placeholder('-'),
                TextEntry::make('icon_url')->label('Icon URL')->placeholder('-')->url(fn ($state): ?string => $state),
                IconEntry::make('is_active')->boolean(),
                IconEntry::make('is_blacklisted')->boolean(),
                TextEntry::make('created_at')->dateTime(),
            ]);
    }
}
