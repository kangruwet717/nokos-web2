<?php

namespace App\Filament\Resources\ProviderWebhookEvents\Pages;

use App\Filament\Resources\ProviderWebhookEvents\ProviderWebhookEventResource;
use Filament\Resources\Pages\CreateRecord;

class CreateProviderWebhookEvent extends CreateRecord
{
    protected static string $resource = ProviderWebhookEventResource::class;
}
