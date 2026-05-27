@php
    $user = auth()->user();
    $money = fn ($amount) => 'Rp'.number_format((float) $amount, 0, ',', '.');
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-sm font-semibold text-emerald-700">Ledger wallet</p>
                <h1 class="text-2xl font-bold text-slate-950">Riwayat wallet</h1>
            </div>
            <a href="{{ route('topup.index') }}" class="inline-flex rounded-lg bg-emerald-600 px-4 py-2 text-sm font-bold text-white hover:bg-emerald-500">Top Up</a>
        </div>
    </x-slot>

    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="grid gap-4 sm:grid-cols-3">
            <div class="rounded-lg border border-slate-200 bg-white p-4">
                <div class="text-sm font-semibold text-slate-500">Saldo tersedia</div>
                <div class="mt-1 text-2xl font-bold text-slate-950">{{ $money($user->availableBalance()) }}</div>
            </div>
            <div class="rounded-lg border border-slate-200 bg-white p-4">
                <div class="text-sm font-semibold text-slate-500">Saldo total</div>
                <div class="mt-1 text-2xl font-bold text-slate-950">{{ $money($user->balance) }}</div>
            </div>
            <div class="rounded-lg border border-slate-200 bg-white p-4">
                <div class="text-sm font-semibold text-slate-500">Saldo tertahan</div>
                <div class="mt-1 text-2xl font-bold text-slate-950">{{ $money($user->reserved_balance) }}</div>
            </div>
        </div>

        <section class="mt-6 rounded-lg border border-slate-200 bg-white">
            <div class="border-b border-slate-100 px-5 py-4">
                <h2 class="font-bold text-slate-950">Mutasi saldo</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-slate-50 text-left text-xs font-bold uppercase text-slate-500">
                        <tr>
                            <th class="px-5 py-3">Tanggal</th>
                            <th class="px-5 py-3">Tipe</th>
                            <th class="px-5 py-3">Arah</th>
                            <th class="px-5 py-3 text-right">Nominal</th>
                            <th class="px-5 py-3 text-right">Saldo setelah</th>
                            <th class="px-5 py-3 text-right">Tertahan setelah</th>
                            <th class="px-5 py-3">Deskripsi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($transactions as $transaction)
                            <tr class="hover:bg-slate-50">
                                <td class="whitespace-nowrap px-5 py-4 text-slate-500">{{ $transaction->created_at->format('d M Y H:i') }}</td>
                                <td class="px-5 py-4 font-semibold text-slate-950">{{ ucfirst(str_replace('_', ' ', $transaction->type)) }}</td>
                                <td class="px-5 py-4">
                                    <span class="rounded-full px-2.5 py-1 text-xs font-bold ring-1 {{ $transaction->direction === 'credit' ? 'bg-emerald-50 text-emerald-700 ring-emerald-200' : 'bg-slate-50 text-slate-700 ring-slate-200' }}">
                                        {{ ucfirst($transaction->direction) }}
                                    </span>
                                </td>
                                <td class="px-5 py-4 text-right font-bold {{ $transaction->direction === 'credit' ? 'text-emerald-700' : 'text-slate-950' }}">
                                    {{ $transaction->direction === 'credit' ? '+' : '-' }}{{ $money($transaction->amount) }}
                                </td>
                                <td class="px-5 py-4 text-right font-semibold text-slate-950">{{ $money($transaction->balance_after) }}</td>
                                <td class="px-5 py-4 text-right font-semibold text-slate-950">{{ $money($transaction->reserved_after) }}</td>
                                <td class="min-w-56 px-5 py-4 text-slate-500">{{ $transaction->description ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-5 py-12 text-center text-slate-500">Belum ada transaksi wallet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="border-t border-slate-100 px-5 py-4">
                {{ $transactions->links() }}
            </div>
        </section>
    </div>
</x-app-layout>
