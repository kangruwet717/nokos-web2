<?php

namespace App\Filament\Resources\WalletTransactions\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class WalletTransactionInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('user.email'),
                TextEntry::make('type')
                    ->badge(),
                TextEntry::make('direction')
                    ->badge(),
                TextEntry::make('amount')
                    ->money('IDR'),
                TextEntry::make('balance_before')
                    ->money('IDR'),
                TextEntry::make('balance_after')
                    ->money('IDR'),
                TextEntry::make('reserved_before')
                    ->money('IDR'),
                TextEntry::make('reserved_after')
                    ->money('IDR'),
                TextEntry::make('status')
                    ->badge(),
                TextEntry::make('description')
                    ->placeholder('-'),
                TextEntry::make('admin.email')
                    ->label('Admin')
                    ->placeholder('-'),
                TextEntry::make('created_at')
                    ->dateTime(),
            ]);
    }
}
