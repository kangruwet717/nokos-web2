<?php

namespace App\Filament\Resources\OperationalAlerts;

use App\Filament\Resources\OperationalAlerts\Pages\ListOperationalAlerts;
use App\Filament\Resources\OperationalAlerts\Pages\ViewOperationalAlert;
use App\Filament\Resources\OperationalAlerts\Schemas\OperationalAlertInfolist;
use App\Filament\Resources\OperationalAlerts\Tables\OperationalAlertsTable;
use App\Models\OperationalAlert;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class OperationalAlertResource extends Resource
{
    protected static ?string $model = OperationalAlert::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedExclamationTriangle;

    protected static ?string $navigationLabel = 'Operational Alerts';

    public static function infolist(Schema $schema): Schema
    {
        return OperationalAlertInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return OperationalAlertsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListOperationalAlerts::route('/'),
            'view' => ViewOperationalAlert::route('/{record}'),
        ];
    }
}
