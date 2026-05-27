<?php

namespace App\Filament\Resources\ProviderSyncScopes\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class ProviderSyncScopeInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('provider.name')->label('Provider')->badge(),
                TextEntry::make('scope_key')->columnSpanFull(),
                TextEntry::make('country_code')->placeholder('-'),
                TextEntry::make('service_code')->placeholder('-'),
                TextEntry::make('status')->badge(),
                TextEntry::make('last_queued_at')->dateTime()->placeholder('-'),
                TextEntry::make('last_synced_at')->dateTime()->placeholder('-'),
                TextEntry::make('last_success_at')->dateTime()->placeholder('-'),
                TextEntry::make('last_failed_at')->dateTime()->placeholder('-'),
                TextEntry::make('error_message')->placeholder('-')->columnSpanFull(),
                TextEntry::make('created_at')->dateTime(),
                TextEntry::make('updated_at')->dateTime(),
            ]);
    }
}
