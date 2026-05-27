@php
    $money = fn ($amount) => 'Rp'.number_format((float) $amount, 0, ',', '.');
    $statusClass = function (string $status): string {
        return match ($status) {
            'paid' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
            'pending' => 'bg-amber-50 text-amber-800 ring-amber-200',
            'failed', 'cancelled', 'expired' => 'bg-rose-50 text-rose-700 ring-rose-200',
            default => 'bg-slate-50 text-slate-700 ring-slate-200',
        };
    };
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-sm font-semibold text-emerald-700">Wallet</p>
                <h1 class="text-2xl font-bold text-slate-950">Top up saldo</h1>
            </div>
            <a href="{{ route('wallet.history') }}" class="inline-flex rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-bold text-slate-800 hover:bg-slate-50">Riwayat wallet</a>
        </div>
    </x-slot>

    <div class="mx-auto grid max-w-7xl gap-6 px-4 sm:px-6 lg:grid-cols-[0.85fr_1.15fr] lg:px-8">
        <section class="rounded-lg border border-slate-200 bg-white">
            <div class="border-b border-slate-100 px-5 py-4">
                <h2 class="font-bold text-slate-950">Buat invoice</h2>
            </div>
            <form method="POST" action="{{ route('topup.store') }}" class="p-5">
                @csrf

                <label for="amount" class="text-sm font-bold text-slate-700">Nominal</label>
                <div class="mt-2 flex rounded-lg border border-slate-300 bg-white shadow-sm focus-within:border-emerald-500 focus-within:ring-1 focus-within:ring-emerald-500">
                    <span class="inline-flex items-center border-r border-slate-200 px-3 text-sm font-bold text-slate-500">Rp</span>
                    <input id="amount" name="amount" type="number" min="10000" max="10000000" step="1000" value="{{ old('amount', 50000) }}" class="block w-full border-0 text-sm focus:ring-0" required>
                </div>
                <x-input-error :messages="$errors->get('amount')" class="mt-2" />
                <p class="mt-2 text-sm text-slate-500">Minimal Rp10.000 dan maksimal Rp10.000.000 per invoice.</p>

                <div class="mt-5 grid grid-cols-2 gap-2 sm:grid-cols-3">
                    @foreach ([10000, 25000, 50000, 100000, 250000, 500000] as $preset)
                        <button type="button" onclick="document.getElementById('amount').value = '{{ $preset }}'" class="rounded-lg border border-slate-300 px-3 py-2 text-sm font-bold text-slate-700 hover:bg-slate-50">
                            {{ $money($preset) }}
                        </button>
                    @endforeach
                </div>

                <button class="mt-6 w-full rounded-lg bg-emerald-600 px-4 py-3 text-sm font-bold text-white hover:bg-emerald-500">
                    Buat Invoice
                </button>
            </form>
        </section>

        <section class="rounded-lg border border-slate-200 bg-white">
            <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
                <h2 class="font-bold text-slate-950">Invoice terbaru</h2>
                <span class="text-sm text-slate-500">{{ $invoices->count() }} invoice</span>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-slate-50 text-left text-xs font-bold uppercase text-slate-500">
                        <tr>
                            <th class="px-5 py-3">Invoice</th>
                            <th class="px-5 py-3 text-right">Nominal</th>
                            <th class="px-5 py-3">Status</th>
                            <th class="px-5 py-3">Dibuat</th>
                            <th class="px-5 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($invoices as $invoice)
                            <tr class="hover:bg-slate-50">
                                <td class="px-5 py-4 font-semibold text-slate-950">{{ $invoice->invoice_no }}</td>
                                <td class="px-5 py-4 text-right font-bold text-slate-950">{{ $money($invoice->amount) }}</td>
                                <td class="px-5 py-4">
                                    <span class="rounded-full px-2.5 py-1 text-xs font-bold ring-1 {{ $statusClass($invoice->status) }}">{{ ucfirst($invoice->status) }}</span>
                                </td>
                                <td class="px-5 py-4 text-slate-500">{{ $invoice->created_at->format('d M Y H:i') }}</td>
                                <td class="px-5 py-4 text-right">
                                    <a href="{{ route('topup.show', $invoice) }}" class="font-bold text-emerald-700 hover:text-emerald-600">Detail</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-5 py-12 text-center text-slate-500">Belum ada invoice top up.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</x-app-layout>
