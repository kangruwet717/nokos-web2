<?php

namespace App\Filament\Resources\AuditLogs\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class AuditLogInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('created_at')
                    ->dateTime(),
                TextEntry::make('actor.email')
                    ->placeholder('System'),
                TextEntry::make('actor_role')
                    ->badge()
                    ->placeholder('-'),
                TextEntry::make('action'),
                TextEntry::make('target_type')
                    ->placeholder('-'),
                TextEntry::make('target_id')
                    ->placeholder('-'),
                TextEntry::make('ip_address')
                    ->placeholder('-'),
                TextEntry::make('user_agent')
                    ->placeholder('-'),
            ]);
    }
}
