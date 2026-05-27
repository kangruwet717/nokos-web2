<?php

namespace App\Filament\Resources\OtpOrders\Tables;

use App\Models\OtpOrder;
use App\Services\Orders\OtpOrderService;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class OtpOrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')->dateTime()->sortable(),
                TextColumn::make('order_no')->searchable(),
                TextColumn::make('user.email')->searchable(),
                TextColumn::make('otpService.name')->label('Service')->searchable(),
                TextColumn::make('country.name')->searchable(),
                TextColumn::make('provider_activation_id')->searchable()->placeholder('-'),
                TextColumn::make('phone_number_masked')->placeholder('-'),
                TextColumn::make('selling_price')->money('IDR')->sortable(),
                TextColumn::make('status')->badge()->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'creating' => 'Creating',
                        'waiting_sms' => 'Waiting SMS',
                        'success' => 'Success',
                        'cancelled' => 'Cancelled',
                        'expired' => 'Expired',
                        'failed' => 'Failed',
                        'refunded' => 'Refunded',
                    ]),
            ])
            ->recordActions([
                ViewAction::make(),
                Action::make('refresh')
                    ->visible(fn (OtpOrder $record): bool => $record->status === 'waiting_sms')
                    ->action(function (OtpOrder $record): void {
                        app(OtpOrderService::class)->refreshStatus($record);
                        Notification::make()->title('Order refreshed')->success()->send();
                    }),
                Action::make('cancel')
                    ->requiresConfirmation()
                    ->visible(fn (OtpOrder $record): bool => $record->canBeCancelled())
                    ->action(function (OtpOrder $record): void {
                        app(OtpOrderService::class)->cancel($record, Auth::user());
                        Notification::make()->title('Order cancelled')->success()->send();
                    }),
                Action::make('refund')
                    ->requiresConfirmation()
                    ->visible(fn (OtpOrder $record): bool => in_array($record->status, ['success', 'failed', 'expired', 'cancelled'], true) && blank($record->refunded_at))
                    ->form([
                        Textarea::make('reason')
                            ->required()
                            ->maxLength(500),
                    ])
                    ->action(function (OtpOrder $record, array $data): void {
                        app(OtpOrderService::class)->manualRefund($record, Auth::user(), $data['reason']);
                        Notification::make()->title('Order refunded')->success()->send();
                    }),
            ]);
    }
}
