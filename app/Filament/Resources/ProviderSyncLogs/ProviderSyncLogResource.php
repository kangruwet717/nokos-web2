<?php

namespace App\Filament\Resources\ProviderSyncLogs;

use App\Filament\Resources\ProviderSyncLogs\Pages\ListProviderSyncLogs;
use App\Filament\Resources\ProviderSyncLogs\Pages\ViewProviderSyncLog;
use App\Filament\Resources\ProviderSyncLogs\Schemas\ProviderSyncLogInfolist;
use App\Filament\Resources\ProviderSyncLogs\Tables\ProviderSyncLogsTable;
use App\Models\ProviderSyncLog;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ProviderSyncLogResource extends Resource
{
    protected static ?string $model = ProviderSyncLog::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    protected static ?string $navigationLabel = 'Sync Logs';

    protected static ?string $modelLabel = 'Sync Log';

    protected static ?string $pluralModelLabel = 'Sync Logs';

    public static function infolist(Schema $schema): Schema
    {
        return ProviderSyncLogInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ProviderSyncLogsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListProviderSyncLogs::route('/'),
            'view' => ViewProviderSyncLog::route('/{record}'),
        ];
    }
}
