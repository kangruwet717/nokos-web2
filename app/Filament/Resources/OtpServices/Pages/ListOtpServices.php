<?php

namespace App\Filament\Resources\OtpServices\Pages;

use App\Filament\Resources\OtpServices\OtpServiceResource;
use Filament\Resources\Pages\ListRecords;

class ListOtpServices extends ListRecords
{
    protected static string $resource = OtpServiceResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
