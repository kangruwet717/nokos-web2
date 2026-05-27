<?php

namespace App\Filament\Resources\ProviderWebhookEvents;

use App\Filament\Resources\ProviderWebhookEvents\Pages\ListProviderWebhookEvents;
use App\Filament\Resources\ProviderWebhookEvents\Pages\ViewProviderWebhookEvent;
use App\Filament\Resources\ProviderWebhookEvents\Schemas\ProviderWebhookEventForm;
use App\Filament\Resources\ProviderWebhookEvents\Schemas\ProviderWebhookEventInfolist;
use App\Filament\Resources\ProviderWebhookEvents\Tables\ProviderWebhookEventsTable;
use App\Models\ProviderWebhookEvent;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ProviderWebhookEventResource extends Resource
{
    protected static ?string $model = ProviderWebhookEvent::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSignal;

    public static function form(Schema $schema): Schema
    {
        return ProviderWebhookEventForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ProviderWebhookEventInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ProviderWebhookEventsTable::configure($table);
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
            'index' => ListProviderWebhookEvents::route('/'),
            'view' => ViewProviderWebhookEvent::route('/{record}'),
        ];
    }
}
