<?php

namespace App\Filament\Resources\ServicePrices\Tables;

use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class ServicePricesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('otpService.name')->label('Service')->searchable()->sortable(),
                TextColumn::make('country.name')->searchable()->sortable(),
                TextColumn::make('provider_price_key')->label('Variant')->searchable()->toggleable(),
                TextColumn::make('provider_price')->money('USD')->sortable(),
                TextColumn::make('selling_price')->money('IDR')->sortable(),
                TextColumn::make('stock_count')->sortable(),
                TextColumn::make('margin_type')->badge(),
                TextColumn::make('margin_value')->sortable(),
                IconColumn::make('is_active')->boolean(),
                TextColumn::make('last_synced_at')->dateTime()->placeholder('-')->sortable(),
            ])
            ->filters([
                SelectFilter::make('provider_id')->relationship('provider', 'name'),
                TernaryFilter::make('is_active'),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ]);
    }
}
