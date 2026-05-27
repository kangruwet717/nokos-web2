<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\OtpOrders\OtpOrderResource;
use App\Models\OtpOrder;
use App\Services\Orders\OtpOrderService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class ActiveOtpOrdersWidget extends TableWidget
{
    protected int | string | array $columnSpan = 'full';

    protected static ?int $sort = 3;

    public function table(Table $table): Table
    {
        return $table
            ->heading('Order OTP aktif dan problem')
            ->description('Order waiting SMS serta order gagal/expired terbaru untuk respons cepat.')
            ->query(fn (): Builder => OtpOrder::query()
                ->with(['user', 'otpService', 'country'])
                ->where(function (Builder $query): void {
                    $query
                        ->where('status', 'waiting_sms')
                        ->orWhere(function (Builder $query): void {
                            $query
                                ->whereIn('status', ['failed', 'expired'])
                                ->where('created_at', '>=', now()->subDay());
                        });
                })
                ->latest()
                ->limit(10))
            ->columns([
                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('order_no')
                    ->label('Order')
                    ->searchable(),
                TextColumn::make('user.email')
                    ->label('User')
                    ->searchable(),
                TextColumn::make('otpService.name')
                    ->label('Service')
                    ->searchable(),
                TextColumn::make('country.name')
                    ->label('Country')
                    ->searchable(),
                TextColumn::make('selling_price')
                    ->label('Harga')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->sortable(),
                TextColumn::make('expires_at')
                    ->label('Expired')
                    ->dateTime()
                    ->placeholder('-'),
            ])
            ->recordUrl(fn (OtpOrder $record): string => OtpOrderResource::getUrl('view', ['record' => $record]))
            ->recordActions([
                Action::make('refresh')
                    ->label('Refresh')
                    ->visible(fn (OtpOrder $record): bool => $record->status === 'waiting_sms')
                    ->action(function (OtpOrder $record): void {
                        app(OtpOrderService::class)->refreshStatus($record);
                        Notification::make()->title('Order refreshed')->success()->send();
                    }),
            ]);
    }
}
