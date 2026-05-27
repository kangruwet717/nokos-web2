<?php

namespace App\Filament\Resources\OtpOrders\Pages;

use App\Filament\Resources\OtpOrders\OtpOrderResource;
use Filament\Resources\Pages\ListRecords;

class ListOtpOrders extends ListRecords
{
    protected static string $resource = OtpOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
