@php
    $user = auth()->user();
    $money = fn ($amount) => 'Rp'.number_format((float) $amount, 0, ',', '.');
    $statusClass = function (string $status): string {
        return match ($status) {
            'paid', 'completed', 'success' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
            'pending', 'waiting_sms' => 'bg-amber-50 text-amber-800 ring-amber-200',
            'cancelled', 'failed', 'expired', 'refunded' => 'bg-rose-50 text-rose-700 ring-rose-200',
            default => 'bg-slate-50 text-slate-700 ring-slate-200',
        };
    };
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-sm font-semibold text-emerald-700">Selamat datang, {{ $user->name }}</p>
                <h1 class="text-2xl font-bold text-slate-950">Dashboard</h1>
            </div>
            <div class="text-sm text-slate-500">Status akun: <span class="font-semibold text-slate-800">{{ ucfirst($user->status) }}</span></div>
        </div>
    </x-slot>

    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="grid gap-4 md:grid-cols-3">
            <div class="rounded-lg border border-slate-200 bg-white p-5">
                <div class="text-sm font-semibold text-slate-500">Saldo tersedia</div>
                <div class="mt-2 text-3xl font-bold text-slate-950">{{ $money($user->availableBalance()) }}</div>
                <a href="{{ route('topup.index') }}" class="mt-4 inline-flex rounded-lg bg-emerald-600 px-4 py-2 text-sm font-bold text-white hover:bg-emerald-500">Top Up</a>
            </div>

            <div class="rounded-lg border border-slate-200 bg-white p-5">
                <div class="text-sm font-semibold text-slate-500">Saldo tertahan</div>
                <div class="mt-2 text-3xl font-bold text-slate-950">{{ $money($user->reserved_balance) }}</div>
                <p class="mt-4 text-sm leading-6 text-slate-500">Saldo tertahan dipakai untuk order OTP yang sedang menunggu kode.</p>
            </div>

            <div class="rounded-lg border border-slate-200 bg-white p-5">
                <div class="text-sm font-semibold text-slate-500">Order aktif</div>
                <div class="mt-2 text-3xl font-bold text-slate-950">{{ $activeOrders->count() }}</div>
                <a href="{{ route('otp.index') }}" class="mt-4 inline-flex rounded-lg border border-slate-300 px-4 py-2 text-sm font-bold text-slate-800 hover:bg-slate-50">Beli OTP</a>
            </div>
        </div>

        <div class="mt-6 grid gap-6 lg:grid-cols-[1fr_0.8fr]">
            <section class="rounded-lg border border-slate-200 bg-white">
                <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
                    <h2 class="font-bold text-slate-950">Order aktif</h2>
                    <a href="{{ route('otp.orders.index') }}" class="text-sm font-bold text-emerald-700 hover:text-emerald-600">Lihat semua</a>
                </div>
                <div class="divide-y divide-slate-100">
                    @forelse ($activeOrders as $order)
                        <a href="{{ route('otp.orders.show', $order) }}" class="grid gap-3 px-5 py-4 hover:bg-slate-50 sm:grid-cols-[1fr_auto] sm:items-center">
                            <div>
                                <div class="font-semibold text-slate-950">{{ $order->otpService->name }} · {{ $order->country->name }}</div>
                                <div class="mt-1 text-sm text-slate-500">{{ $order->order_no }} · {{ $order->phone_number_masked ?? $order->phone_number ?? 'Menunggu nomor' }}</div>
                            </div>
                            <div class="flex items-center gap-3 sm:justify-end">
                                <span class="rounded-full px-2.5 py-1 text-xs font-bold ring-1 {{ $statusClass($order->status) }}">{{ ucfirst(str_replace('_', ' ', $order->status)) }}</span>
                                <span class="text-sm font-bold text-slate-950">{{ $money($order->selling_price) }}</span>
                            </div>
                        </a>
                    @empty
                        <div class="px-5 py-10 text-center">
                            <div class="font-semibold text-slate-950">Belum ada order aktif</div>
                            <p class="mt-2 text-sm text-slate-500">Pilih service dan negara untuk mulai membeli nomor OTP.</p>
                        </div>
                    @endforelse
                </div>
            </section>

            <section class="rounded-lg border border-slate-200 bg-white">
                <div class="border-b border-slate-100 px-5 py-4">
                    <h2 class="font-bold text-slate-950">Invoice pending</h2>
                </div>
                <div class="divide-y divide-slate-100">
                    @forelse ($pendingInvoices as $invoice)
                        <a href="{{ route('topup.show', $invoice) }}" class="flex items-center justify-between gap-4 px-5 py-4 hover:bg-slate-50">
                            <div>
                                <div class="font-semibold text-slate-950">{{ $invoice->invoice_no }}</div>
                                <div class="mt-1 text-sm text-slate-500">{{ $invoice->created_at->format('d M Y H:i') }}</div>
                            </div>
                            <div class="text-right text-sm font-bold text-slate-950">{{ $money($invoice->amount) }}</div>
                        </a>
                    @empty
                        <div class="px-5 py-10 text-center text-sm text-slate-500">Tidak ada invoice yang menunggu pembayaran.</div>
                    @endforelse
                </div>
            </section>
        </div>

        <div class="mt-6 grid gap-6 lg:grid-cols-2">
            <section class="rounded-lg border border-slate-200 bg-white">
                <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
                    <h2 class="font-bold text-slate-950">Transaksi wallet terbaru</h2>
                    <a href="{{ route('wallet.history') }}" class="text-sm font-bold text-emerald-700 hover:text-emerald-600">Detail</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($recentTransactions as $transaction)
                                <tr>
                                    <td class="px-5 py-3">
                                        <div class="font-semibold text-slate-950">{{ ucfirst(str_replace('_', ' ', $transaction->type)) }}</div>
                                        <div class="text-xs text-slate-500">{{ $transaction->created_at->format('d M Y H:i') }}</div>
                                    </td>
                                    <td class="px-5 py-3 text-right font-bold {{ $transaction->direction === 'credit' ? 'text-emerald-700' : 'text-slate-950' }}">
                                        {{ $transaction->direction === 'credit' ? '+' : '-' }}{{ $money($transaction->amount) }}
                                    </td>
                                </tr>
                            @empty
                                <tr><td class="px-5 py-10 text-center text-slate-500">Belum ada transaksi wallet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="rounded-lg border border-slate-200 bg-white">
                <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
                    <h2 class="font-bold text-slate-950">Order terbaru</h2>
                    <a href="{{ route('otp.orders.index') }}" class="text-sm font-bold text-emerald-700 hover:text-emerald-600">Detail</a>
                </div>
                <div class="divide-y divide-slate-100">
                    @forelse ($recentOrders as $order)
                        <a href="{{ route('otp.orders.show', $order) }}" class="flex items-center justify-between gap-4 px-5 py-4 hover:bg-slate-50">
                            <div>
                                <div class="font-semibold text-slate-950">{{ $order->otpService->name }}</div>
                                <div class="mt-1 text-sm text-slate-500">{{ $order->country->name }} · {{ $order->created_at->format('d M Y H:i') }}</div>
                            </div>
                            <span class="rounded-full px-2.5 py-1 text-xs font-bold ring-1 {{ $statusClass($order->status) }}">{{ ucfirst(str_replace('_', ' ', $order->status)) }}</span>
                        </a>
                    @empty
                        <div class="px-5 py-10 text-center text-sm text-slate-500">Belum ada riwayat order.</div>
                    @endforelse
                </div>
            </section>
        </div>
    </div>
</x-app-layout>
