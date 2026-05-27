<?php

namespace App\Filament\Resources\OtpOrders\Pages;

use App\Filament\Resources\OtpOrders\OtpOrderResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewOtpOrder extends ViewRecord
{
    protected static string $resource = OtpOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
