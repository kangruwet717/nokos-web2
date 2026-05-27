<?php

namespace App\Filament\Resources\ServicePrices;

use App\Filament\Resources\ServicePrices\Pages\EditServicePrice;
use App\Filament\Resources\ServicePrices\Pages\ListServicePrices;
use App\Filament\Resources\ServicePrices\Pages\ViewServicePrice;
use App\Filament\Resources\ServicePrices\Schemas\ServicePriceForm;
use App\Filament\Resources\ServicePrices\Schemas\ServicePriceInfolist;
use App\Filament\Resources\ServicePrices\Tables\ServicePricesTable;
use App\Models\ServicePrice;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ServicePriceResource extends Resource
{
    protected static ?string $model = ServicePrice::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCurrencyDollar;

    public static function form(Schema $schema): Schema
    {
        return ServicePriceForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ServicePriceInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ServicePricesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListServicePrices::route('/'),
            'view' => ViewServicePrice::route('/{record}'),
            'edit' => EditServicePrice::route('/{record}/edit'),
        ];
    }
}
