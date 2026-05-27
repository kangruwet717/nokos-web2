<?php

namespace App\Filament\Resources\ProviderWebhookEvents\Pages;

use App\Filament\Resources\ProviderWebhookEvents\ProviderWebhookEventResource;
use Filament\Resources\Pages\ListRecords;

class ListProviderWebhookEvents extends ListRecords
{
    protected static string $resource = ProviderWebhookEventResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
