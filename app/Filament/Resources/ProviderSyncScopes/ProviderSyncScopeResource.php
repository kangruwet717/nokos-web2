<?php

namespace App\Filament\Resources\ProviderSyncScopes;

use App\Filament\Resources\ProviderSyncScopes\Pages\ListProviderSyncScopes;
use App\Filament\Resources\ProviderSyncScopes\Pages\ViewProviderSyncScope;
use App\Filament\Resources\ProviderSyncScopes\Schemas\ProviderSyncScopeInfolist;
use App\Filament\Resources\ProviderSyncScopes\Tables\ProviderSyncScopesTable;
use App\Models\ProviderSyncScope;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ProviderSyncScopeResource extends Resource
{
    protected static ?string $model = ProviderSyncScope::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowPath;

    protected static ?string $navigationLabel = 'Sync Scopes';

    protected static ?string $modelLabel = 'Sync Scope';

    protected static ?string $pluralModelLabel = 'Sync Scopes';

    public static function infolist(Schema $schema): Schema
    {
        return ProviderSyncScopeInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ProviderSyncScopesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListProviderSyncScopes::route('/'),
            'view' => ViewProviderSyncScope::route('/{record}'),
        ];
    }
}
