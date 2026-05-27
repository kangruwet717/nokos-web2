<?php

namespace App\Filament\Resources\ProviderSyncLogs\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class ProviderSyncLogInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('provider.name')->label('Provider')->badge(),
                TextEntry::make('sync_type')->badge(),
                TextEntry::make('status')->badge(),
                TextEntry::make('country_code')->placeholder('-'),
                TextEntry::make('service_code')->placeholder('-'),
                TextEntry::make('createdBy.email')->label('Queued by')->placeholder('-'),
                TextEntry::make('processed_items')->numeric(),
                TextEntry::make('failed_items')->numeric(),
                TextEntry::make('duration_ms')
                    ->label('Duration')
                    ->formatStateUsing(fn ($state): string => $state === null ? '-' : number_format(((int) $state) / 1000, 2).'s'),
                TextEntry::make('started_at')->dateTime()->placeholder('-'),
                TextEntry::make('finished_at')->dateTime()->placeholder('-'),
                TextEntry::make('error_message')->placeholder('-')->columnSpanFull(),
                TextEntry::make('meta')
                    ->formatStateUsing(fn ($state): string => $state ? json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) : '-')
                    ->columnSpanFull(),
                TextEntry::make('created_at')->dateTime(),
                TextEntry::make('updated_at')->dateTime(),
            ]);
    }
}
