<?php

namespace App\Filament\Resources\ServicePrices\Pages;

use App\Filament\Resources\ServicePrices\ServicePriceResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewServicePrice extends ViewRecord
{
    protected static string $resource = ServicePriceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
