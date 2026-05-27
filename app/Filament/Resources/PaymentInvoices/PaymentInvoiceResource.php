<?php

namespace App\Filament\Resources\PaymentInvoices;

use App\Filament\Resources\PaymentInvoices\Pages\ListPaymentInvoices;
use App\Filament\Resources\PaymentInvoices\Pages\ViewPaymentInvoice;
use App\Filament\Resources\PaymentInvoices\Schemas\PaymentInvoiceForm;
use App\Filament\Resources\PaymentInvoices\Schemas\PaymentInvoiceInfolist;
use App\Filament\Resources\PaymentInvoices\Tables\PaymentInvoicesTable;
use App\Models\PaymentInvoice;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PaymentInvoiceResource extends Resource
{
    protected static ?string $model = PaymentInvoice::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCreditCard;

    protected static ?string $navigationLabel = 'Payment Invoices';

    public static function form(Schema $schema): Schema
    {
        return PaymentInvoiceForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return PaymentInvoiceInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PaymentInvoicesTable::configure($table);
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
            'index' => ListPaymentInvoices::route('/'),
            'view' => ViewPaymentInvoice::route('/{record}'),
        ];
    }
}
