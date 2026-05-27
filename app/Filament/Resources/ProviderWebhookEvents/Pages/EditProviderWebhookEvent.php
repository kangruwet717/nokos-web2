<?php

namespace App\Filament\Resources\ProviderWebhookEvents\Pages;

use App\Filament\Resources\ProviderWebhookEvents\ProviderWebhookEventResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditProviderWebhookEvent extends EditRecord
{
    protected static string $resource = ProviderWebhookEventResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
