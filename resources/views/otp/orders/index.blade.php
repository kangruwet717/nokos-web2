@php
    $money = fn ($amount) => 'Rp'.number_format((float) $amount, 0, ',', '.');
    $statusClass = function (string $status): string {
        return match ($status) {
            'completed' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
            'pending', 'waiting_sms' => 'bg-amber-50 text-amber-800 ring-amber-200',
            'cancelled', 'failed', 'expired', 'refunded' => 'bg-rose-50 text-rose-700 ring-rose-200',
            default => 'bg-slate-50 text-slate-700 ring-slate-200',
        };
    };
    $orderSubtitle = function ($order): string {
        if ($order->phone_number_masked || $order->phone_number) {
            return $order->phone_number_masked ?? $order->phone_number;
        }

        return match ($order->status) {
            'creating' => 'Membuat order',
            'waiting_sms' => 'Menunggu SMS',
            'failed' => 'Gagal dibuat',
            'cancelled' => 'Dibatalkan',
            'expired' => 'Kadaluarsa',
            'success' => 'Selesai',
            'refunded' => 'Direfund',
            default => ucfirst(str_replace('_', ' ', $order->status)),
        };
    };
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-sm font-semibold text-emerald-700">OTP</p>
                <h1 class="text-2xl font-bold text-slate-950">Riwayat order</h1>
            </div>
            <a href="{{ route('otp.index') }}" class="inline-flex rounded-lg bg-emerald-600 px-4 py-2 text-sm font-bold text-white hover:bg-emerald-500">Beli OTP</a>
        </div>
    </x-slot>

    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <section class="rounded-lg border border-slate-200 bg-white">
            <div class="border-b border-slate-100 px-5 py-4">
                <h2 class="font-bold text-slate-950">Semua order</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-slate-50 text-left text-xs font-bold uppercase text-slate-500">
                        <tr>
                            <th class="px-5 py-3">Order</th>
                            <th class="px-5 py-3">Service</th>
                            <th class="px-5 py-3">Negara</th>
                            <th class="px-5 py-3 text-right">Harga</th>
                            <th class="px-5 py-3">Status</th>
                            <th class="px-5 py-3">Dibuat</th>
                            <th class="px-5 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($orders as $order)
                            <tr class="hover:bg-slate-50">
                                <td class="px-5 py-4">
                                    <div class="font-semibold text-slate-950">{{ $order->order_no }}</div>
                                    <div class="mt-1 text-xs text-slate-500">{{ $orderSubtitle($order) }}</div>
                                </td>
                                <td class="px-5 py-4 font-semibold text-slate-950">{{ $order->otpService->name }}</td>
                                <td class="px-5 py-4 text-slate-600">{{ $order->country->name }}</td>
                                <td class="px-5 py-4 text-right font-bold text-slate-950">{{ $money($order->selling_price) }}</td>
                                <td class="px-5 py-4">
                                    <span class="rounded-full px-2.5 py-1 text-xs font-bold ring-1 {{ $statusClass($order->status) }}">{{ ucfirst(str_replace('_', ' ', $order->status)) }}</span>
                                </td>
                                <td class="whitespace-nowrap px-5 py-4 text-slate-500">{{ $order->created_at->format('d M Y H:i') }}</td>
                                <td class="px-5 py-4 text-right">
                                    <a href="{{ route('otp.orders.show', $order) }}" class="font-bold text-emerald-700 hover:text-emerald-600">Detail</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-5 py-12 text-center">
                                    <div class="font-bold text-slate-950">Belum ada order OTP</div>
                                    <p class="mt-2 text-sm text-slate-500">Mulai dari katalog untuk memilih service dan negara.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="border-t border-slate-100 px-5 py-4">{{ $orders->links() }}</div>
        </section>
    </div>
</x-app-layout>
