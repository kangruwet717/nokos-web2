<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-sm font-semibold text-emerald-700">Support</p>
                <h1 class="text-2xl font-bold text-slate-950">Buat ticket support</h1>
            </div>
            <a href="{{ route('support.index') }}" class="inline-flex rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-bold text-slate-800 hover:bg-slate-50">Kembali</a>
        </div>
    </x-slot>

    <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
        <section class="rounded-lg border border-slate-200 bg-white">
            <div class="border-b border-slate-100 px-5 py-4">
                <h2 class="font-bold text-slate-950">Detail laporan</h2>
            </div>
            <form method="POST" action="{{ route('support.store') }}" class="space-y-5 p-5">
                @csrf

                <div>
                    <label for="category" class="text-sm font-bold text-slate-700">Kategori</label>
                    <select id="category" name="category" class="mt-2 block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500" required>
                        @foreach (['payment' => 'Payment', 'order' => 'Order OTP', 'refund' => 'Refund', 'account' => 'Akun', 'abuse' => 'Abuse report', 'other' => 'Lainnya'] as $value => $label)
                            <option value="{{ $value }}" @selected(old('category') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('category')" class="mt-2" />
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="otp_order_id" class="text-sm font-bold text-slate-700">Order terkait</label>
                        <select id="otp_order_id" name="otp_order_id" class="mt-2 block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                            <option value="">Tidak ada</option>
                            @foreach ($orders as $order)
                                <option value="{{ $order->id }}" @selected((string) old('otp_order_id') === (string) $order->id)>{{ $order->order_no }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="payment_invoice_id" class="text-sm font-bold text-slate-700">Invoice terkait</label>
                        <select id="payment_invoice_id" name="payment_invoice_id" class="mt-2 block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                            <option value="">Tidak ada</option>
                            @foreach ($invoices as $invoice)
                                <option value="{{ $invoice->id }}" @selected((string) old('payment_invoice_id') === (string) $invoice->id)>{{ $invoice->invoice_no }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div>
                    <label for="subject" class="text-sm font-bold text-slate-700">Subjek</label>
                    <input id="subject" name="subject" value="{{ old('subject') }}" maxlength="160" class="mt-2 block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500" required>
                    <x-input-error :messages="$errors->get('subject')" class="mt-2" />
                </div>

                <div>
                    <label for="message" class="text-sm font-bold text-slate-700">Pesan</label>
                    <textarea id="message" name="message" rows="7" class="mt-2 block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500" required>{{ old('message') }}</textarea>
                    <x-input-error :messages="$errors->get('message')" class="mt-2" />
                </div>

                <button class="rounded-lg bg-emerald-600 px-5 py-3 text-sm font-bold text-white hover:bg-emerald-500">Kirim Ticket</button>
            </form>
        </section>
    </div>
</x-app-layout>
