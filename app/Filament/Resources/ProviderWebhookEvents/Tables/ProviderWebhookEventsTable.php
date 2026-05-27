<?php

namespace App\Filament\Resources\ProviderWebhookEvents\Tables;

use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ProviderWebhookEventsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')->dateTime()->sortable(),
                TextColumn::make('provider')->badge(),
                TextColumn::make('activation_id')->searchable()->placeholder('-'),
                TextColumn::make('otpOrder.order_no')->searchable()->placeholder('-'),
                IconColumn::make('signature_valid')->boolean(),
                IconColumn::make('processed')->boolean(),
                TextColumn::make('processed_at')->dateTime()->placeholder('-'),
                TextColumn::make('error_message')->limit(50)->placeholder('-'),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
            ]);
    }
}
