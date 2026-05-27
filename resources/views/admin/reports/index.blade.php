<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-sm font-semibold text-emerald-700">Admin</p>
                <h1 class="text-2xl font-bold text-slate-950">Reports & Export</h1>
            </div>
            <a href="/admin" class="inline-flex rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-bold text-slate-800 hover:bg-slate-50">Panel Admin</a>
        </div>
    </x-slot>

    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <section class="rounded-lg border border-slate-200 bg-white">
            <div class="border-b border-slate-100 px-5 py-4">
                <h2 class="font-bold text-slate-950">Filter export</h2>
                <p class="mt-1 text-sm text-slate-500">Tanggal opsional. Kosongkan untuk export semua data.</p>
            </div>

            <form method="GET" class="grid gap-4 p-5 sm:grid-cols-2 lg:grid-cols-4">
                <div>
                    <label for="date_from" class="text-sm font-bold text-slate-700">Tanggal mulai</label>
                    <input id="date_from" name="date_from" type="date" value="{{ request('date_from') }}" class="mt-2 block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                </div>
                <div>
                    <label for="date_to" class="text-sm font-bold text-slate-700">Tanggal akhir</label>
                    <input id="date_to" name="date_to" type="date" value="{{ request('date_to') }}" class="mt-2 block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                </div>
                <div class="flex items-end">
                    <button class="rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-bold text-slate-800 hover:bg-slate-50">Terapkan</button>
                </div>
            </form>
        </section>

        @php
            $query = array_filter([
                'date_from' => request('date_from'),
                'date_to' => request('date_to'),
            ]);
            $exports = [
                ['Payment invoices', 'Invoice top up, status, fee, dan payment method.', route('admin.reports.payment-invoices', $query)],
                ['Wallet transactions', 'Ledger saldo lengkap termasuk before/after balance.', route('admin.reports.wallet-transactions', $query)],
                ['OTP orders', 'Order OTP lengkap dengan service, negara, harga, dan status.', route('admin.reports.otp-orders', $query)],
                ['Profit report', 'Order sukses/refund dengan selling price, provider cost, dan margin.', route('admin.reports.profit', $query)],
            ];
        @endphp

        <section class="mt-6 grid gap-4 md:grid-cols-2">
            @foreach ($exports as [$title, $description, $url])
                <div class="rounded-lg border border-slate-200 bg-white p-5">
                    <h2 class="font-bold text-slate-950">{{ $title }}</h2>
                    <p class="mt-2 text-sm leading-6 text-slate-600">{{ $description }}</p>
                    <a href="{{ $url }}" class="mt-5 inline-flex rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-bold text-white hover:bg-emerald-500">Download CSV</a>
                </div>
            @endforeach
        </section>
    </div>
</x-app-layout>
