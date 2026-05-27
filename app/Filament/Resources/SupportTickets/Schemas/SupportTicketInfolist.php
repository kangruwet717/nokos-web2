<?php

namespace App\Filament\Resources\SupportTickets\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class SupportTicketInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('ticket_no'),
                TextEntry::make('user.email'),
                TextEntry::make('category')->badge(),
                TextEntry::make('subject'),
                TextEntry::make('status')->badge(),
                TextEntry::make('priority')->badge(),
                TextEntry::make('otpOrder.order_no')->label('Order')->placeholder('-'),
                TextEntry::make('paymentInvoice.invoice_no')->label('Invoice')->placeholder('-'),
                TextEntry::make('messages')
                    ->label('Messages')
                    ->formatStateUsing(fn ($record): string => $record->messages()
                        ->with('user')
                        ->oldest()
                        ->get()
                        ->map(fn ($message): string => '['.$message->created_at->format('d M Y H:i').'] '
                            .($message->is_admin ? 'Admin' : ($message->user?->email ?? 'User'))
                            .': '.$message->message)
                        ->implode("\n\n"))
                    ->columnSpanFull(),
                TextEntry::make('last_replied_at')->dateTime()->placeholder('-'),
                TextEntry::make('closed_at')->dateTime()->placeholder('-'),
                TextEntry::make('created_at')->dateTime(),
            ]);
    }
}
