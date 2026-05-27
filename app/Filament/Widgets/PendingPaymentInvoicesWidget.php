<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\PaymentInvoices\PaymentInvoiceResource;
use App\Models\PaymentInvoice;
use App\Services\Payments\PaymentService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class PendingPaymentInvoicesWidget extends TableWidget
{
    protected int | string | array $columnSpan = 'full';

    protected static ?int $sort = 2;

    public function table(Table $table): Table
    {
        return $table
            ->heading('Top up pending')
            ->description('Invoice pending terlama yang perlu dipantau atau direconcile.')
            ->query(fn (): Builder => PaymentInvoice::query()
                ->with('user')
                ->where('status', 'pending')
                ->oldest()
                ->limit(10))
            ->columns([
                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('invoice_no')
                    ->label('Invoice')
                    ->searchable(),
                TextColumn::make('user.email')
                    ->label('User')
                    ->searchable(),
                TextColumn::make('amount')
                    ->label('Nominal')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('external_id')
                    ->label('External ID')
                    ->placeholder('-')
                    ->limit(24),
                TextColumn::make('expired_at')
                    ->label('Expired')
                    ->dateTime()
                    ->placeholder('-')
                    ->sortable(),
            ])
            ->recordUrl(fn (PaymentInvoice $record): string => PaymentInvoiceResource::getUrl('view', ['record' => $record]))
            ->recordActions([
                Action::make('reconcile')
                    ->label('Reconcile')
                    ->visible(fn (PaymentInvoice $record): bool => filled($record->external_id))
                    ->action(function (PaymentInvoice $record): void {
                        app(PaymentService::class)->reconcile($record);
                        Notification::make()->title('Invoice reconciled')->success()->send();
                    }),
                Action::make('expire')
                    ->label('Expire')
                    ->requiresConfirmation()
                    ->visible(fn (PaymentInvoice $record): bool => $record->status === 'pending')
                    ->action(function (PaymentInvoice $record): void {
                        app(PaymentService::class)->expireInvoice($record);
                        Notification::make()->title('Invoice expired')->success()->send();
                    }),
            ]);
    }
}
