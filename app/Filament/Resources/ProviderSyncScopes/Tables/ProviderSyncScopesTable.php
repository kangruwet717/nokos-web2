<?php

namespace App\Filament\Resources\ProviderSyncScopes\Tables;

use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ProviderSyncScopesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('updated_at', 'desc')
            ->poll('5s')
            ->columns([
                TextColumn::make('updated_at')->dateTime()->sortable(),
                TextColumn::make('provider.name')->label('Provider')->searchable()->badge(),
                TextColumn::make('scope_key')->searchable()->limit(40),
                TextColumn::make('country_code')->searchable()->placeholder('-'),
                TextColumn::make('service_code')->searchable()->placeholder('-'),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'queued' => 'warning',
                        'running' => 'info',
                        'success' => 'success',
                        'failed' => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),
                TextColumn::make('last_queued_at')->dateTime()->placeholder('-')->sortable(),
                TextColumn::make('last_success_at')->dateTime()->placeholder('-')->sortable(),
                TextColumn::make('last_failed_at')->dateTime()->placeholder('-')->sortable(),
                TextColumn::make('error_message')->limit(50)->placeholder('-'),
            ])
            ->filters([
                SelectFilter::make('provider_id')
                    ->label('Provider')
                    ->relationship('provider', 'name'),
                SelectFilter::make('status')
                    ->options([
                        'idle' => 'Idle',
                        'queued' => 'Queued',
                        'running' => 'Running',
                        'success' => 'Success',
                        'failed' => 'Failed',
                    ]),
            ])
            ->recordActions([
                ViewAction::make(),
            ]);
    }
}
