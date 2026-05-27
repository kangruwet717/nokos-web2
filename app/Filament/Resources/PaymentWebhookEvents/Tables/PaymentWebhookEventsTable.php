<?php

namespace App\Filament\Resources\PaymentWebhookEvents\Tables;

use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PaymentWebhookEventsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('event_type')
                    ->searchable(),
                TextColumn::make('external_id')
                    ->searchable()
                    ->placeholder('-'),
                TextColumn::make('invoice.invoice_no')
                    ->searchable()
                    ->placeholder('-'),
                IconColumn::make('signature_valid')
                    ->boolean(),
                IconColumn::make('processed')
                    ->boolean(),
                TextColumn::make('processed_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextColumn::make('error_message')
                    ->limit(40)
                    ->placeholder('-'),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
            ]);
    }
}
