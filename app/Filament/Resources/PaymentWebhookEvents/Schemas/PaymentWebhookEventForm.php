<?php

namespace App\Filament\Resources\PaymentWebhookEvents\Schemas;

use Filament\Schemas\Schema;

class PaymentWebhookEventForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                //
            ]);
    }
}
