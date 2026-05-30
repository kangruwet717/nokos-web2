<?php

namespace App\Filament\Resources\Users\Tables;

use App\Models\User;
use App\Services\Audit\AuditLogService;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('email')
                    ->label('Email address')
                    ->searchable(),
                TextColumn::make('role')
                    ->badge()
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->sortable(),
                TextColumn::make('balance')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('reserved_balance')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('kyc_status')
                    ->badge()
                    ->sortable(),
                TextColumn::make('email_verified_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('role')
                    ->options([
                        'user' => 'User',
                        'admin' => 'Admin',
                        'super_admin' => 'Super Admin',
                    ]),
                SelectFilter::make('status')
                    ->options([
                        'active' => 'Active',
                        'suspended' => 'Suspended',
                        'banned' => 'Banned',
                    ]),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                Action::make('suspend')
                    ->requiresConfirmation()
                    ->visible(fn (User $record): bool => $record->status === 'active')
                    ->action(function (User $record): void {
                        $record->forceFill(['status' => 'suspended'])->save();
                        app(AuditLogService::class)->record('user.suspended', Auth::user(), $record);
                        Notification::make()->title('User suspended')->success()->send();
                    }),
                Action::make('unsuspend')
                    ->requiresConfirmation()
                    ->visible(fn (User $record): bool => $record->status === 'suspended')
                    ->action(function (User $record): void {
                        $record->forceFill(['status' => 'active'])->save();
                        app(AuditLogService::class)->record('user.unsuspended', Auth::user(), $record);
                        Notification::make()->title('User reactivated')->success()->send();
                    }),
                Action::make('adjust_balance')
                    ->label('Adjust balance')
                    ->url(fn (User $record): string => route('admin.wallet-adjustments.edit', $record)),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                ]),
            ]);
    }
}
