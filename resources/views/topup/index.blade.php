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
    $methodLabel = fn (?string $method, string $provider = '') => match ($method) {
        'qris1' => 'QRIS 1',
        'qris2' => 'QRIS 2',
        default => 'QRIS',
    };
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-sm font-semibold text-emerald-700">Wallet</p>
                <h1 class="text-2xl font-bold text-slate-950">Top up saldo</h1>
            </div>
            <a href="{{ route('wallet.history') }}" class="inline-flex rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-bold text-slate-800 hover:bg-slate-50">Riwayat wallet</a>
        </div>
    </x-slot>

    <div class="mx-auto grid max-w-7xl gap-6 px-4 sm:px-6 lg:grid-cols-[0.9fr_1.1fr] lg:px-8">
        <section
            x-data="{
                amount: Number(@js((int) old('amount', 50000))),
                method: @js(old('payment_method', 'qris1')),
                minimumAmount() {
                    return this.method === 'qris2' ? 5000 : 10000;
                },
                format(value) {
                    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(Number(value || 0));
                },
                setAmount(value) {
                    this.amount = Number(value);
                },
            }"
            class="rounded-lg border border-slate-200 bg-white"
        >
            <div class="border-b border-slate-100 px-5 py-4">
                <h2 class="font-bold text-slate-950">Buat invoice</h2>
                <p class="mt-1 text-sm text-slate-500">Pilih nominal dan channel QRIS yang ingin dipakai.</p>
            </div>

            <form method="POST" action="{{ route('topup.store') }}" class="p-5">
                @csrf

                <label for="amount" class="text-sm font-bold text-slate-700">Nominal saldo</label>
                <div class="mt-2 flex rounded-lg border border-slate-300 bg-white shadow-sm focus-within:border-emerald-500 focus-within:ring-1 focus-within:ring-emerald-500">
                    <span class="inline-flex items-center border-r border-slate-200 px-3 text-sm font-bold text-slate-500">Rp</span>
                    <input
                        id="amount"
                        x-model.number="amount"
                        name="amount"
                        type="number"
                        :min="minimumAmount()"
                        max="10000000"
                        step="1000"
                        class="block w-full border-0 text-sm font-semibold focus:ring-0"
                        required
                    >
                </div>
                <x-input-error :messages="$errors->get('amount')" class="mt-2" />

                <div class="mt-4 grid grid-cols-2 gap-2 sm:grid-cols-3">
                    @foreach ([5000, 10000, 25000, 50000, 100000, 250000, 500000] as $preset)
                        <button type="button" @click="setAmount({{ $preset }})" class="rounded-lg border border-slate-300 px-3 py-2 text-sm font-bold text-slate-700 hover:bg-slate-50">
                            {{ $money($preset) }}
                        </button>
                    @endforeach
                </div>

                <div class="mt-6">
                    <div class="text-sm font-bold text-slate-700">Metode pembayaran</div>
                    <div class="mt-3 grid gap-3 sm:grid-cols-2">
                        <label
                            class="cursor-pointer rounded-lg border p-4 transition"
                            :class="method === 'qris1' ? 'border-emerald-400 bg-emerald-50 ring-1 ring-emerald-200' : 'border-slate-200 bg-white hover:border-emerald-300 hover:bg-emerald-50'"
                        >
                            <input x-model="method" type="radio" name="payment_method" value="qris1" class="sr-only">
                            <span class="flex items-start justify-between gap-3">
                                <span>
                                    <span class="block text-base font-black text-slate-950">QRIS 1</span>
                                    <span class="mt-2 inline-flex rounded-full bg-white px-2.5 py-1 text-xs font-bold text-slate-600 ring-1 ring-slate-200">Min. Rp10.000</span>
                                </span>
                            </span>
                            <span class="mt-4 block text-sm leading-6 text-slate-500">Cocok untuk pembayaran lewat halaman checkout.</span>
                        </label>

                        <label
                            class="cursor-pointer rounded-lg border p-4 transition"
                            :class="method === 'qris2' ? 'border-emerald-400 bg-emerald-50 ring-1 ring-emerald-200' : 'border-slate-200 bg-white hover:border-emerald-300 hover:bg-emerald-50'"
                        >
                            <input x-model="method" type="radio" name="payment_method" value="qris2" class="sr-only">
                            <span class="flex items-start justify-between gap-3">
                                <span>
                                    <span class="block text-base font-black text-slate-950">QRIS 2</span>
                                    <span class="mt-2 inline-flex rounded-full bg-white px-2.5 py-1 text-xs font-bold text-slate-600 ring-1 ring-slate-200">Min. Rp5.000</span>
                                </span>
                            </span>
                            <span class="mt-4 block text-sm leading-6 text-slate-500">Nominal bayar dibuat unik agar mutasi lebih mudah dicocokkan.</span>
                        </label>
                    </div>
                    <x-input-error :messages="$errors->get('payment_method')" class="mt-2" />
                </div>

                <div class="mt-6 rounded-lg bg-slate-50 p-4">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <div class="text-xs font-bold uppercase text-slate-500">Saldo masuk</div>
                            <div class="mt-1 text-xl font-black text-slate-950" x-text="format(amount)"></div>
                        </div>
                        <div class="text-right">
                            <div class="text-xs font-bold uppercase text-slate-500">Channel</div>
                            <div class="mt-1 text-sm font-black text-slate-950" x-text="method === 'qris2' ? 'QRIS 2' : 'QRIS 1'"></div>
                        </div>
                    </div>
                    <p x-show="method === 'qris2'" class="mt-3 text-sm font-medium leading-6 text-amber-800">
                        Nominal yang harus dibayar dapat berbeda sedikit dari saldo masuk untuk keamanan pencocokan mutasi.
                    </p>
                </div>

                <button class="mt-6 w-full rounded-lg bg-emerald-600 px-4 py-3 text-sm font-bold text-white hover:bg-emerald-500">
                    Buat Invoice
                </button>
            </form>
        </section>

        <section class="rounded-lg border border-slate-200 bg-white">
            <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
                <div>
                    <h2 class="font-bold text-slate-950">Invoice terbaru</h2>
                    <p class="mt-1 text-sm text-slate-500">Pantau pembayaran terakhir dari satu tempat.</p>
                </div>
                <span class="rounded-full bg-slate-50 px-3 py-1 text-sm font-bold text-slate-600">{{ $invoices->count() }}</span>
            </div>

            <div class="divide-y divide-slate-100">
                @forelse ($invoices as $invoice)
                    <a href="{{ route('topup.show', $invoice) }}" class="grid gap-4 px-5 py-4 transition hover:bg-slate-50 md:grid-cols-[1fr_auto] md:items-center">
                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="truncate font-bold text-slate-950">{{ $invoice->invoice_no }}</span>
                                <span class="rounded-full px-2.5 py-1 text-xs font-bold ring-1 {{ $statusClass($invoice->status) }}">{{ ucfirst($invoice->status) }}</span>
                            </div>
                            <div class="mt-2 flex flex-wrap gap-x-3 gap-y-1 text-sm text-slate-500">
                                <span>{{ $methodLabel($invoice->payment_method, $invoice->provider) }}</span>
                                <span>{{ $invoice->created_at->format('d M Y H:i') }}</span>
                            </div>
                        </div>
                        <div class="md:text-right">
                            <div class="text-lg font-black text-slate-950">{{ $money($invoice->amount) }}</div>
                            @if (bccomp((string) $invoice->amount, (string) $invoice->net_amount, 2) !== 0)
                                <div class="mt-1 text-xs font-semibold text-slate-500">Saldo {{ $money($invoice->net_amount) }}</div>
                            @endif
                        </div>
                    </a>
                @empty
                    <div class="px-5 py-12 text-center text-sm text-slate-500">Belum ada invoice top up.</div>
                @endforelse
            </div>
        </section>
    </div>
</x-app-layout>
