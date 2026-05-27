<?php

namespace App\Filament\Resources\OtpServices\Pages;

use App\Filament\Resources\OtpServices\OtpServiceResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewOtpService extends ViewRecord
{
    protected static string $resource = OtpServiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
