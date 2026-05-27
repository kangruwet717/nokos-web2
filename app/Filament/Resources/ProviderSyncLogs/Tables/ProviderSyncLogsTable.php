<?php

namespace App\Filament\Resources\ProviderSyncLogs\Tables;

use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ProviderSyncLogsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->poll('5s')
            ->columns([
                TextColumn::make('created_at')->dateTime()->sortable(),
                TextColumn::make('provider.name')->label('Provider')->searchable()->badge(),
                TextColumn::make('sync_type')->label('Type')->badge()->sortable(),
                TextColumn::make('country_code')->searchable()->placeholder('-'),
                TextColumn::make('service_code')->searchable()->placeholder('-'),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'queued' => 'warning',
                        'running' => 'info',
                        'success' => 'success',
                        'failed' => 'danger',
                        'skipped' => 'gray',
                        default => 'gray',
                    })
                    ->sortable(),
                TextColumn::make('processed_items')->numeric()->sortable(),
                TextColumn::make('failed_items')->numeric()->sortable(),
                TextColumn::make('duration_ms')
                    ->label('Duration')
                    ->formatStateUsing(fn ($state): string => $state === null ? '-' : number_format(((int) $state) / 1000, 2).'s')
                    ->sortable(),
                TextColumn::make('createdBy.email')->label('Queued by')->searchable()->placeholder('-'),
                TextColumn::make('error_message')->limit(50)->placeholder('-'),
            ])
            ->filters([
                SelectFilter::make('provider_id')
                    ->label('Provider')
                    ->relationship('provider', 'name'),
                SelectFilter::make('sync_type')
                    ->options([
                        'full' => 'Full',
                        'scoped' => 'Scoped',
                    ]),
                SelectFilter::make('status')
                    ->options([
                        'queued' => 'Queued',
                        'running' => 'Running',
                        'success' => 'Success',
                        'failed' => 'Failed',
                        'skipped' => 'Skipped',
                    ]),
            ])
            ->recordActions([
                ViewAction::make(),
            ]);
    }
}
