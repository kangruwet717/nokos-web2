<?php

namespace App\Filament\Resources\AuditLogs\Tables;

use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AuditLogsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('actor.email')
                    ->searchable()
                    ->placeholder('System'),
                TextColumn::make('actor_role')
                    ->badge()
                    ->placeholder('-'),
                TextColumn::make('action')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('target_type')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('target_id')
                    ->sortable(),
                TextColumn::make('ip_address')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
            ]);
    }
}
