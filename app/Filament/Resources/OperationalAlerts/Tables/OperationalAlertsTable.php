<?php

namespace App\Filament\Resources\OperationalAlerts\Tables;

use App\Models\OperationalAlert;
use App\Services\Operations\OperationalAlertService;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class OperationalAlertsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                IconColumn::make('is_open')
                    ->label('Open')
                    ->state(fn (OperationalAlert $record): bool => $record->isOpen())
                    ->boolean(),
                TextColumn::make('created_at')->dateTime()->sortable(),
                TextColumn::make('severity')->badge()->sortable(),
                TextColumn::make('type')->badge()->sortable(),
                TextColumn::make('title')->searchable(),
                TextColumn::make('message')->limit(80)->searchable(),
                TextColumn::make('resolved_at')->dateTime()->placeholder('-')->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('severity')
                    ->options([
                        'info' => 'Info',
                        'warning' => 'Warning',
                        'critical' => 'Critical',
                    ]),
                SelectFilter::make('type')
                    ->options([
                        'provider_low_balance' => 'Provider low balance',
                        'pending_invoice_overdue' => 'Pending invoice overdue',
                        'payment_webhook_errors' => 'Payment webhook errors',
                        'provider_webhook_errors' => 'Provider webhook errors',
                    ]),
                SelectFilter::make('status')
                    ->options([
                        'open' => 'Open',
                        'resolved' => 'Resolved',
                    ])
                    ->query(function ($query, array $data) {
                        return match ($data['value'] ?? null) {
                            'open' => $query->whereNull('resolved_at'),
                            'resolved' => $query->whereNotNull('resolved_at'),
                            default => $query,
                        };
                    }),
            ])
            ->recordActions([
                ViewAction::make(),
                Action::make('resolve')
                    ->requiresConfirmation()
                    ->visible(fn (OperationalAlert $record): bool => $record->isOpen())
                    ->action(function (OperationalAlert $record): void {
                        app(OperationalAlertService::class)->resolve($record, Auth::user());
                        Notification::make()->title('Alert resolved')->success()->send();
                    }),
                Action::make('reopen')
                    ->visible(fn (OperationalAlert $record): bool => ! $record->isOpen())
                    ->action(function (OperationalAlert $record): void {
                        $record->forceFill([
                            'resolved_at' => null,
                            'resolved_by_user_id' => null,
                        ])->save();

                        Notification::make()->title('Alert reopened')->success()->send();
                    }),
            ]);
    }
}
