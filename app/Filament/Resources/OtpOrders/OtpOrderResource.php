<?php

namespace App\Filament\Resources\OtpOrders;

use App\Filament\Resources\OtpOrders\Pages\ListOtpOrders;
use App\Filament\Resources\OtpOrders\Pages\ViewOtpOrder;
use App\Filament\Resources\OtpOrders\Schemas\OtpOrderForm;
use App\Filament\Resources\OtpOrders\Schemas\OtpOrderInfolist;
use App\Filament\Resources\OtpOrders\Tables\OtpOrdersTable;
use App\Models\OtpOrder;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class OtpOrderResource extends Resource
{
    protected static ?string $model = OtpOrder::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDevicePhoneMobile;

    public static function form(Schema $schema): Schema
    {
        return OtpOrderForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return OtpOrderInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return OtpOrdersTable::configure($table);
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
            'index' => ListOtpOrders::route('/'),
            'view' => ViewOtpOrder::route('/{record}'),
        ];
    }
}
