<?php

namespace App\Filament\Resources\OtpServices;

use App\Filament\Resources\OtpServices\Pages\EditOtpService;
use App\Filament\Resources\OtpServices\Pages\ListOtpServices;
use App\Filament\Resources\OtpServices\Pages\ViewOtpService;
use App\Filament\Resources\OtpServices\Schemas\OtpServiceForm;
use App\Filament\Resources\OtpServices\Schemas\OtpServiceInfolist;
use App\Filament\Resources\OtpServices\Tables\OtpServicesTable;
use App\Models\OtpService;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class OtpServiceResource extends Resource
{
    protected static ?string $model = OtpService::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSquares2x2;

    public static function form(Schema $schema): Schema
    {
        return OtpServiceForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return OtpServiceInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return OtpServicesTable::configure($table);
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
            'index' => ListOtpServices::route('/'),
            'view' => ViewOtpService::route('/{record}'),
            'edit' => EditOtpService::route('/{record}/edit'),
        ];
    }
}
