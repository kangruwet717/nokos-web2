<?php

namespace App\Filament\Resources\Countries\Tables;

use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class CountriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('provider.name')->sortable(),
                TextColumn::make('provider_code')->searchable(),
                TextColumn::make('iso_code')->placeholder('-')->searchable(),
                TextColumn::make('name')->searchable()->sortable(),
                IconColumn::make('is_active')->boolean(),
                IconColumn::make('is_blacklisted')->boolean(),
                TextColumn::make('updated_at')->dateTime()->sortable(),
            ])
            ->filters([
                SelectFilter::make('provider_id')->relationship('provider', 'name'),
                TernaryFilter::make('is_active'),
                TernaryFilter::make('is_blacklisted'),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ]);
    }
}
