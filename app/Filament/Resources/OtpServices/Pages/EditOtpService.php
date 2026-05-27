<?php

namespace App\Filament\Resources\OtpServices\Pages;

use App\Filament\Resources\OtpServices\OtpServiceResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditOtpService extends EditRecord
{
    protected static string $resource = OtpServiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
