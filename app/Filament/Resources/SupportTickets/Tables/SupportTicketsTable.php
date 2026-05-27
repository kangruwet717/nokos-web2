<?php

namespace App\Filament\Resources\SupportTickets\Tables;

use App\Models\SupportTicket;
use App\Services\Support\SupportTicketService;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class SupportTicketsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')->dateTime()->sortable(),
                TextColumn::make('ticket_no')->searchable(),
                TextColumn::make('user.email')->searchable(),
                TextColumn::make('category')->badge()->sortable(),
                TextColumn::make('subject')->searchable()->limit(45),
                TextColumn::make('status')->badge()->sortable(),
                TextColumn::make('priority')->badge()->sortable(),
                TextColumn::make('last_replied_at')->dateTime()->sortable()->placeholder('-'),
            ])
            ->defaultSort('last_replied_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'open' => 'Open',
                        'pending_user' => 'Pending User',
                        'closed' => 'Closed',
                    ]),
                SelectFilter::make('category')
                    ->options([
                        'payment' => 'Payment',
                        'order' => 'Order',
                        'refund' => 'Refund',
                        'account' => 'Account',
                        'abuse' => 'Abuse',
                        'other' => 'Other',
                    ]),
            ])
            ->recordActions([
                ViewAction::make(),
                Action::make('reply')
                    ->visible(fn (SupportTicket $record): bool => ! $record->isClosed())
                    ->form([
                        Textarea::make('message')
                            ->required()
                            ->maxLength(5000),
                    ])
                    ->action(function (SupportTicket $record, array $data): void {
                        app(SupportTicketService::class)->adminReply($record, Auth::user(), $data['message']);
                        Notification::make()->title('Reply sent')->success()->send();
                    }),
                Action::make('close')
                    ->requiresConfirmation()
                    ->visible(fn (SupportTicket $record): bool => ! $record->isClosed())
                    ->action(function (SupportTicket $record): void {
                        app(SupportTicketService::class)->close($record, Auth::user());
                        Notification::make()->title('Ticket closed')->success()->send();
                    }),
                Action::make('reopen')
                    ->visible(fn (SupportTicket $record): bool => $record->isClosed())
                    ->action(function (SupportTicket $record): void {
                        $record->forceFill([
                            'status' => 'open',
                            'closed_at' => null,
                            'last_replied_at' => now(),
                        ])->save();

                        Notification::make()->title('Ticket reopened')->success()->send();
                    }),
            ]);
    }
}
