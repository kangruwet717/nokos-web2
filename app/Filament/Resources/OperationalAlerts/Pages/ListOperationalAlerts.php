<?php

namespace App\Filament\Resources\OperationalAlerts\Pages;

use App\Filament\Resources\OperationalAlerts\OperationalAlertResource;
use App\Services\Operations\OperationalAlertService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListOperationalAlerts extends ListRecords
{
    protected static string $resource = OperationalAlertResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('checkNow')
                ->label('Check now')
                ->action(function (): void {
                    app(OperationalAlertService::class)->check();
                    Notification::make()->title('Operational alerts checked')->success()->send();
                }),
        ];
    }
}
