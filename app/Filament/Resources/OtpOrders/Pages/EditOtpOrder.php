<?php

namespace App\Filament\Resources\OtpOrders\Pages;

use App\Filament\Resources\OtpOrders\OtpOrderResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditOtpOrder extends EditRecord
{
    protected static string $resource = OtpOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
