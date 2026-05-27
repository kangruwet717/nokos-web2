<?php

namespace App\Filament\Resources\OtpOrders\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class OtpOrderInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('order_no'),
                TextEntry::make('user.email'),
                TextEntry::make('otpService.name')->label('Service'),
                TextEntry::make('country.name'),
                TextEntry::make('provider_activation_id')->placeholder('-'),
                TextEntry::make('phone_number')->placeholder('-'),
                TextEntry::make('provider_cost')->money('USD'),
                TextEntry::make('selling_price')->money('IDR'),
                TextEntry::make('status')->badge(),
                TextEntry::make('sms_code')->placeholder('-'),
                TextEntry::make('sms_text_masked')->placeholder('-'),
                TextEntry::make('expires_at')->dateTime()->placeholder('-'),
                TextEntry::make('completed_at')->dateTime()->placeholder('-'),
                TextEntry::make('cancelled_at')->dateTime()->placeholder('-'),
                TextEntry::make('refunded_at')->dateTime()->placeholder('-'),
                TextEntry::make('refund_reason')->placeholder('-'),
                TextEntry::make('created_at')->dateTime(),
            ]);
    }
}
