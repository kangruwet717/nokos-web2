<?php

namespace App\Filament\Resources\ProviderWebhookEvents\Pages;

use App\Filament\Resources\ProviderWebhookEvents\ProviderWebhookEventResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewProviderWebhookEvent extends ViewRecord
{
    protected static string $resource = ProviderWebhookEventResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
