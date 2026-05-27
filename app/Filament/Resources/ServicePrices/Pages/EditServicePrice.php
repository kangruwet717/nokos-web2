<?php

namespace App\Filament\Resources\ServicePrices\Pages;

use App\Filament\Resources\ServicePrices\ServicePriceResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditServicePrice extends EditRecord
{
    protected static string $resource = ServicePriceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
