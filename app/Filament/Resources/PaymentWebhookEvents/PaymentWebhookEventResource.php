<?php

namespace App\Filament\Resources\PaymentWebhookEvents;

use App\Filament\Resources\PaymentWebhookEvents\Pages\ListPaymentWebhookEvents;
use App\Filament\Resources\PaymentWebhookEvents\Pages\ViewPaymentWebhookEvent;
use App\Filament\Resources\PaymentWebhookEvents\Schemas\PaymentWebhookEventForm;
use App\Filament\Resources\PaymentWebhookEvents\Schemas\PaymentWebhookEventInfolist;
use App\Filament\Resources\PaymentWebhookEvents\Tables\PaymentWebhookEventsTable;
use App\Models\PaymentWebhookEvent;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PaymentWebhookEventResource extends Resource
{
    protected static ?string $model = PaymentWebhookEvent::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowPath;

    protected static ?string $navigationLabel = 'Payment Webhooks';

    public static function form(Schema $schema): Schema
    {
        return PaymentWebhookEventForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return PaymentWebhookEventInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PaymentWebhookEventsTable::configure($table);
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
            'index' => ListPaymentWebhookEvents::route('/'),
            'view' => ViewPaymentWebhookEvent::route('/{record}'),
        ];
    }
}
