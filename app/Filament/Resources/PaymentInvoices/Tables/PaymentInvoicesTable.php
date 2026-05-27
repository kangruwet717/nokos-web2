<?php

namespace App\Filament\Resources\PaymentInvoices\Tables;

use App\Models\PaymentInvoice;
use App\Services\Payments\PaymentService;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class PaymentInvoicesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('invoice_no')
                    ->searchable(),
                TextColumn::make('user.email')
                    ->searchable(),
                TextColumn::make('external_id')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('amount')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('fee')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->sortable(),
                TextColumn::make('payment_method')
                    ->placeholder('-'),
                TextColumn::make('paid_at')
                    ->dateTime()
                    ->placeholder('-')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'paid' => 'Paid',
                        'expired' => 'Expired',
                        'failed' => 'Failed',
                        'cancelled' => 'Cancelled',
                    ]),
            ])
            ->recordActions([
                ViewAction::make(),
                Action::make('reconcile')
                    ->visible(fn (PaymentInvoice $record): bool => $record->status !== 'paid' && filled($record->external_id))
                    ->action(function (PaymentInvoice $record): void {
                        app(PaymentService::class)->reconcile($record);
                        Notification::make()->title('Invoice reconciled')->success()->send();
                    }),
            ]);
    }
}
