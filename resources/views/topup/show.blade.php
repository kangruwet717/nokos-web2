@php
    $money = fn ($amount) => 'Rp'.number_format((float) $amount, 0, ',', '.');
    $statusClass = match ($invoice->status) {
        'paid' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
        'pending' => 'bg-amber-50 text-amber-800 ring-amber-200',
        'failed', 'cancelled', 'expired' => 'bg-rose-50 text-rose-700 ring-rose-200',
        default => 'bg-slate-50 text-slate-700 ring-slate-200',
    };
    $checkoutUrl = $invoice->checkoutUrl();
    $paymentCode = $invoice->paymentCode();
    $qrImage = $invoice->qrImage();
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-sm font-semibold text-emerald-700">Invoice top up</p>
                <h1 class="break-all text-2xl font-bold text-slate-950">{{ $invoice->invoice_no }}</h1>
            </div>
            <a href="{{ route('topup.index') }}" class="inline-flex rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-bold text-slate-800 hover:bg-slate-50">Kembali</a>
        </div>
    </x-slot>

    <div class="mx-auto grid max-w-5xl gap-6 px-4 sm:px-6 lg:grid-cols-[1fr_0.8fr] lg:px-8">
        <section class="space-y-6">
            @if (session('status'))
                <div class="rounded-lg border border-emerald-200 bg-emerald-50 p-4 text-sm font-semibold text-emerald-800">
                    {{ session('status') }}
                </div>
            @endif

            @if ($errors->has('payment'))
                <div class="rounded-lg border border-amber-200 bg-amber-50 p-4 text-sm font-semibold leading-6 text-amber-900">
                    {{ $errors->first('payment') }}
                </div>
            @endif

            @if ($invoice->status === 'pending')
                <div class="rounded-lg border border-emerald-200 bg-emerald-50 p-5">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <p class="text-sm font-bold text-emerald-800">Pembayaran menunggu</p>
                            <h2 class="mt-1 text-xl font-bold text-slate-950">Selesaikan pembayaran DompetX</h2>
                            <p class="mt-1 text-sm text-emerald-900">Saldo otomatis masuk setelah webhook atau cek status mendeteksi pembayaran berhasil.</p>
                        </div>
                        @if ($checkoutUrl)
                            <a href="{{ $checkoutUrl }}" target="_blank" rel="noopener" class="inline-flex justify-center rounded-lg bg-emerald-600 px-5 py-3 text-sm font-bold text-white hover:bg-emerald-500">
                                Bayar Sekarang
                            </a>
                        @else
                            <span class="rounded-lg bg-white px-4 py-3 text-sm font-bold text-amber-800 ring-1 ring-amber-200">
                                Link pembayaran belum tersedia
                            </span>
                        @endif
                    </div>
                </div>
            @endif

            <div class="rounded-lg border border-slate-200 bg-white">
            <div class="border-b border-slate-100 px-5 py-4">
                <h2 class="font-bold text-slate-950">Detail pembayaran</h2>
            </div>
            <div class="grid gap-4 p-5 sm:grid-cols-2">
                <div class="rounded-lg bg-slate-50 p-4">
                    <div class="text-sm font-semibold text-slate-500">Nominal</div>
                    <div class="mt-1 text-2xl font-bold text-slate-950">{{ $money($invoice->amount) }}</div>
                </div>
                <div class="rounded-lg bg-slate-50 p-4">
                    <div class="text-sm font-semibold text-slate-500">Status</div>
                    <div class="mt-2">
                        <span class="rounded-full px-2.5 py-1 text-xs font-bold ring-1 {{ $statusClass }}">{{ ucfirst($invoice->status) }}</span>
                    </div>
                </div>
                <div>
                    <div class="text-sm font-semibold text-slate-500">Provider</div>
                    <div class="mt-1 font-bold text-slate-950">{{ strtoupper($invoice->provider) }}</div>
                </div>
                <div>
                    <div class="text-sm font-semibold text-slate-500">Kadaluarsa</div>
                    <div class="mt-1 font-bold text-slate-950">{{ $invoice->expired_at?->format('d M Y H:i') ?? '-' }}</div>
                </div>
                <div>
                    <div class="text-sm font-semibold text-slate-500">Dibuat</div>
                    <div class="mt-1 font-bold text-slate-950">{{ $invoice->created_at->format('d M Y H:i') }}</div>
                </div>
                <div>
                    <div class="text-sm font-semibold text-slate-500">Dibayar</div>
                    <div class="mt-1 font-bold text-slate-950">{{ $invoice->paid_at?->format('d M Y H:i') ?? '-' }}</div>
                </div>
                @if ($invoice->payment_method)
                    <div>
                        <div class="text-sm font-semibold text-slate-500">Metode</div>
                        <div class="mt-1 font-bold text-slate-950">{{ strtoupper($invoice->payment_method) }}</div>
                    </div>
                @endif
                @if ($invoice->external_id)
                    <div>
                        <div class="text-sm font-semibold text-slate-500">ID DompetX</div>
                        <div class="mt-1 break-all font-bold text-slate-950">{{ $invoice->external_id }}</div>
                    </div>
                @endif
            </div>

            <div class="border-t border-slate-100 p-5">
                <div class="flex flex-wrap gap-3">
                    @if ($checkoutUrl && $invoice->status === 'pending')
                        <a href="{{ $checkoutUrl }}" target="_blank" rel="noopener" class="inline-flex rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-bold text-white hover:bg-emerald-500">
                            Buka Pembayaran
                        </a>
                    @endif

                    <form method="POST" action="{{ route('topup.reconcile', $invoice) }}">
                        @csrf
                        <button class="rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-bold text-slate-800 hover:bg-slate-50">Cek Status</button>
                    </form>
                </div>
            </div>
            </div>
        </section>

        <aside class="space-y-6">
            @if ($qrImage || $paymentCode || $checkoutUrl)
                <section class="rounded-lg border border-slate-200 bg-white p-5">
                    <h2 class="font-bold text-slate-950">Akses pembayaran</h2>

                    @if ($qrImage)
                        <div class="mt-4 rounded-lg bg-slate-50 p-4">
                            <img src="{{ $qrImage }}" alt="QR pembayaran" class="mx-auto h-56 w-56 rounded-lg object-contain">
                        </div>
                    @endif

                    @if ($paymentCode)
                        <div class="mt-4">
                            <div class="text-sm font-semibold text-slate-500">Kode pembayaran</div>
                            <div class="mt-2 break-all rounded-lg bg-slate-50 p-4 font-mono text-sm font-bold text-slate-950">{{ $paymentCode }}</div>
                        </div>
                    @endif

                    @if ($checkoutUrl)
                        <div class="mt-4">
                            <div class="text-sm font-semibold text-slate-500">Link checkout</div>
                            <div class="mt-2 break-all rounded-lg bg-slate-50 p-4 text-sm font-semibold text-slate-700">{{ $checkoutUrl }}</div>
                        </div>
                    @endif
                </section>
            @endif

            <section class="rounded-lg border border-slate-200 bg-white p-5">
                <h2 class="font-bold text-slate-950">Instruksi</h2>
            <ol class="mt-4 space-y-3 text-sm leading-6 text-slate-600">
                <li><span class="font-bold text-slate-950">1.</span> Klik Bayar Sekarang untuk membuka checkout DompetX.</li>
                <li><span class="font-bold text-slate-950">2.</span> Selesaikan pembayaran sebelum waktu kadaluarsa.</li>
                <li><span class="font-bold text-slate-950">3.</span> Gunakan cek status jika saldo belum masuk setelah pembayaran berhasil.</li>
            </ol>
            <div class="mt-5 rounded-lg bg-amber-50 p-4 text-sm font-medium leading-6 text-amber-900">
                Invoice final tidak bisa dipakai ulang. Buat invoice baru jika status sudah expired, failed, atau cancelled.
            </div>
            </section>
        </aside>
    </div>

    @if ($invoice->status === 'pending')
        <script>
            window.setInterval(async () => {
                try {
                    const response = await fetch(@json(route('topup.status', $invoice)), {
                        headers: { Accept: 'application/json' },
                    });
                    const payload = await response.json();

                    if (payload.status && payload.status !== 'pending') {
                        window.location.reload();
                    }
                } catch (error) {
                    //
                }
            }, 10000);
        </script>
    @endif
</x-app-layout>
