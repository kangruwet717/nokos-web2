<?php

namespace App\Filament\Resources\WalletTransactions\Tables;

use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class WalletTransactionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('user.email')
                    ->searchable(),
                TextColumn::make('type')
                    ->badge()
                    ->sortable(),
                TextColumn::make('direction')
                    ->badge()
                    ->sortable(),
                TextColumn::make('amount')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('balance_after')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('reserved_after')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->options([
                        'topup' => 'Top up',
                        'order_hold' => 'Order hold',
                        'order_charge' => 'Order charge',
                        'refund' => 'Refund',
                        'adjustment' => 'Adjustment',
                        'promo' => 'Promo',
                    ]),
                SelectFilter::make('direction')
                    ->options([
                        'credit' => 'Credit',
                        'debit' => 'Debit',
                        'hold' => 'Hold',
                        'release' => 'Release',
                    ]),
            ])
            ->recordActions([
                ViewAction::make(),
            ]);
    }
}
