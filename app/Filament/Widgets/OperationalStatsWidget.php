<?php

namespace App\Filament\Widgets;

use App\Models\OtpOrder;
use App\Models\PaymentInvoice;
use App\Models\Provider;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class OperationalStatsWidget extends StatsOverviewWidget
{
    protected ?string $heading = 'Operasional hari ini';

    protected ?string $description = 'Ringkasan transaksi, order OTP, dan kesehatan provider.';

    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $today = now()->startOfDay();

        $paidToday = PaymentInvoice::query()
            ->where('status', 'paid')
            ->where('paid_at', '>=', $today);

        $successfulOrdersToday = OtpOrder::query()
            ->where('status', 'success')
            ->where('completed_at', '>=', $today);

        $pendingTopUps = PaymentInvoice::query()->where('status', 'pending')->count();
        $waitingOrders = OtpOrder::query()->where('status', 'waiting_sms')->count();
        $problemOrders = OtpOrder::query()
            ->whereIn('status', ['failed', 'expired'])
            ->where('created_at', '>=', now()->subDay())
            ->count();
        $activeUsersToday = User::query()
            ->where('last_login_at', '>=', $today)
            ->count();
        $smsbower = Provider::query()->where('code', 'smsbower')->first();

        return [
            Stat::make('Top up paid', $this->rupiah((string) $paidToday->sum('net_amount')))
                ->description($paidToday->count().' invoice paid hari ini')
                ->color('success'),
            Stat::make('Revenue OTP', $this->rupiah((string) $successfulOrdersToday->sum('selling_price')))
                ->description($successfulOrdersToday->count().' order sukses hari ini')
                ->color('info'),
            Stat::make('Margin OTP', $this->rupiah((string) $successfulOrdersToday->sum('margin_amount')))
                ->description('Margin dari order sukses hari ini')
                ->color('success'),
            Stat::make('Top up pending', number_format($pendingTopUps, 0, ',', '.'))
                ->description('Invoice menunggu pembayaran')
                ->color($pendingTopUps > 0 ? 'warning' : 'gray'),
            Stat::make('Order aktif', number_format($waitingOrders, 0, ',', '.'))
                ->description($problemOrders.' problem order dalam 24 jam')
                ->color($problemOrders > 0 ? 'danger' : 'warning'),
            Stat::make('User aktif', number_format($activeUsersToday, 0, ',', '.'))
                ->description('Login sejak awal hari')
                ->color('gray'),
            Stat::make('Saldo SMSBower', $smsbower?->last_balance ? '$'.number_format((float) $smsbower->last_balance, 2, '.', ',') : '-')
                ->description($smsbower?->balance_checked_at ? 'Dicek '.$smsbower->balance_checked_at->diffForHumans() : 'Belum pernah dicek')
                ->color($smsbower?->last_balance && (float) $smsbower->last_balance > 1 ? 'success' : 'danger'),
            Stat::make('Sync katalog', $smsbower?->updated_at ? $smsbower->updated_at->diffForHumans() : '-')
                ->description('Provider SMSBower')
                ->color('gray'),
        ];
    }

    private function rupiah(string $amount): string
    {
        return 'Rp'.number_format((float) $amount, 0, ',', '.');
    }
}
