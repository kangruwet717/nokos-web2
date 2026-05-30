<?php

namespace App\Filament\Resources\Providers\Tables;

use App\Support\ProviderSyncStatus;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ProvidersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->poll('5s')
            ->columns([
                TextColumn::make('code')->searchable(),
                TextColumn::make('name')->searchable(),
                TextColumn::make('base_url')->limit(40),
                IconColumn::make('is_active')->boolean(),
                TextColumn::make('last_balance')->money('USD')->placeholder('-'),
                TextColumn::make('balance_checked_at')->dateTime()->placeholder('-'),
                TextColumn::make('sync_status')
                    ->label('Sync status')
                    ->state(fn ($record): string => $record->code === 'smsbower'
                        ? ProviderSyncStatus::current()['label']
                        : '-')
                    ->description(fn ($record): ?string => $record->code === 'smsbower'
                        ? ProviderSyncStatus::current()['message']
                        : null)
                    ->badge()
                    ->color(fn ($record): string => $record->code === 'smsbower'
                        ? ProviderSyncStatus::color(ProviderSyncStatus::current()['status'])
                        : 'gray'),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                Action::make('sync')
                    ->label('Queue sync')
                    ->visible(fn ($record): bool => $record->code === 'smsbower')
                    ->url(fn ($record): string => route('admin.providers.queue-sync', $record)),
            ]);
    }
}
