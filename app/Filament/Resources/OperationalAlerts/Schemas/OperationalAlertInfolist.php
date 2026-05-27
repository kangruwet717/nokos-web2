<?php

namespace App\Filament\Resources\OperationalAlerts\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class OperationalAlertInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('severity')->badge(),
                TextEntry::make('type')->badge(),
                TextEntry::make('dedupe_key'),
                TextEntry::make('title'),
                TextEntry::make('message')->columnSpanFull(),
                TextEntry::make('metadata')
                    ->formatStateUsing(fn ($state): string => $state ? json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) : '-')
                    ->columnSpanFull(),
                TextEntry::make('resolved_at')->dateTime()->placeholder('-'),
                TextEntry::make('resolvedBy.email')->label('Resolved by')->placeholder('-'),
                TextEntry::make('created_at')->dateTime(),
                TextEntry::make('updated_at')->dateTime(),
            ]);
    }
}
