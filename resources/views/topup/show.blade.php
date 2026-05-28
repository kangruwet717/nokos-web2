@php
    $money = fn ($amount) => 'Rp'.number_format((float) $amount, 0, ',', '.');
    $statusLabel = match ($invoice->status) {
        'paid' => 'Sudah dibayar',
        'expired' => 'Kadaluarsa',
        'failed' => 'Gagal',
        'cancelled' => 'Dibatalkan',
        default => 'Menunggu pembayaran',
    };
    $statusClass = match ($invoice->status) {
        'paid' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
        'pending' => 'bg-amber-50 text-amber-800 ring-amber-200',
        'failed', 'cancelled', 'expired' => 'bg-rose-50 text-rose-700 ring-rose-200',
        default => 'bg-slate-50 text-slate-700 ring-slate-200',
    };
    $checkoutUrl = $invoice->checkoutUrl();
    $qrImage = $invoice->qrImage();
    $displayQrImage = $qrImage ?: ($checkoutUrl ? 'https://api.qrserver.com/v1/create-qr-code/?size=360x360&data='.rawurlencode($checkoutUrl) : null);
    $methodLabel = match ($invoice->payment_method) {
        'qris1' => 'QRIS 1',
        'qris2' => 'QRIS 2',
        default => 'QRIS',
    };
    $isUniqueAmount = bccomp((string) $invoice->amount, (string) $invoice->net_amount, 2) !== 0;
    $isPending = $invoice->status === 'pending';
    $balance = auth()->user()?->availableBalance() ?? '0';
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm font-semibold text-emerald-700">Top Up Saldo</p>
                <h1 class="text-2xl font-bold text-slate-950">Pembayaran QRIS</h1>
                <p class="mt-1 text-sm text-slate-500">Scan QR, bayar nominal exact, saldo masuk otomatis setelah terdeteksi.</p>
            </div>
            <a href="{{ route('topup.index') }}" class="inline-flex rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-bold text-slate-800 hover:bg-slate-50">
                Kembali
            </a>
        </div>
    </x-slot>

    <div class="mx-auto grid max-w-6xl gap-5 px-4 sm:px-6 lg:grid-cols-[1fr_360px] lg:px-8">
        <main class="space-y-5">
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

            <section class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
                <div class="bg-gradient-to-br from-sky-50 via-white to-emerald-50 p-5 sm:p-7">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div>
                            <span class="inline-flex rounded-full px-3 py-1 text-xs font-bold uppercase tracking-wide ring-1 {{ $statusClass }}">{{ $statusLabel }}</span>
                            <p class="mt-3 break-all text-xs font-semibold text-slate-500">{{ $invoice->invoice_no }}</p>
                        </div>
                        @if ($invoice->expired_at && $isPending)
                            <div class="rounded-lg bg-white/80 px-3 py-2 text-right text-xs font-semibold text-slate-600 ring-1 ring-slate-200">
                                Kadaluarsa<br>
                                <span class="text-slate-950">{{ $invoice->expired_at->format('d M Y H:i') }}</span>
                            </div>
                        @endif
                    </div>

                    <div class="mt-6 grid gap-6 lg:grid-cols-[220px_1fr] lg:items-center">
                        <div class="rounded-lg bg-white p-3 shadow-sm ring-1 ring-slate-200">
                            @if ($displayQrImage)
                                <img src="{{ $displayQrImage }}" alt="QRIS pembayaran" class="aspect-square w-full rounded-lg object-contain">
                            @else
                                <div class="flex aspect-square items-center justify-center rounded-lg bg-slate-50 p-5 text-center text-sm font-bold leading-6 text-slate-500">
                                    QR pembayaran belum tersedia.
                                </div>
                            @endif
                        </div>

                        <div x-data="{ copied: false, copy(value) { navigator.clipboard.writeText(value); this.copied = true; setTimeout(() => this.copied = false, 1200); } }">
                            <p class="text-xs font-black uppercase text-slate-400">Bayar exact</p>
                            <div class="mt-1 flex flex-wrap items-center gap-3">
                                <div class="text-4xl font-black tracking-normal text-slate-950 sm:text-5xl">{{ $money($invoice->amount) }}</div>
                                <button type="button" x-on:click="copy(@js((string) (int) $invoice->amount))" class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-xs font-bold text-slate-700 hover:bg-slate-50">
                                    <span x-show="!copied">Salin</span>
                                    <span x-cloak x-show="copied">Tersalin</span>
                                </button>
                            </div>

                            <div class="mt-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-bold text-emerald-800">
                                Saldo masuk: {{ $money($invoice->net_amount) }}
                                @if ($isUniqueAmount)
                                    <span class="font-semibold text-emerald-700">(nominal unik untuk verifikasi otomatis)</span>
                                @endif
                            </div>

                            <ol class="mt-4 space-y-2 text-sm leading-6 text-slate-600">
                                <li><span class="font-bold text-slate-950">1.</span> Buka aplikasi e-wallet atau m-banking.</li>
                                <li><span class="font-bold text-slate-950">2.</span> Scan QRIS di samping.</li>
                                <li><span class="font-bold text-slate-950">3.</span> Masukkan nominal {{ $money($invoice->amount) }} persis.</li>
                                <li><span class="font-bold text-slate-950">4.</span> Setelah bayar, klik cek pembayaran jika saldo belum masuk.</li>
                            </ol>

                            <div class="mt-5 flex flex-col gap-3 sm:flex-row">
                                <form method="POST" action="{{ route('topup.reconcile', $invoice) }}" class="sm:flex-1">
                                    @csrf
                                    <button class="w-full rounded-lg bg-sky-600 px-5 py-3 text-sm font-bold text-white hover:bg-sky-500">
                                        Cek Pembayaran
                                    </button>
                                </form>

                                @if ($checkoutUrl && $isPending)
                                    <a href="{{ $checkoutUrl }}" target="_blank" rel="noopener" class="inline-flex justify-center rounded-lg border border-slate-300 bg-white px-5 py-3 text-sm font-bold text-slate-800 hover:bg-slate-50">
                                        Buka Checkout
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </section>

        </main>

        <aside class="space-y-5">
            <section class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-xs font-black uppercase text-slate-400">Saldo saat ini</p>
                <div class="mt-2 text-3xl font-black text-slate-950">{{ $money($balance) }}</div>
            </section>

            <section class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex items-center gap-3">
                    <div class="flex h-14 w-14 items-center justify-center rounded-lg bg-slate-50 text-sm font-black text-slate-950 ring-1 ring-slate-200">QRIS</div>
                    <div>
                        <h2 class="font-bold text-slate-950">Bayar pakai QRIS</h2>
                        <p class="mt-1 text-xs text-slate-500">Satu QR untuk semua aplikasi.</p>
                    </div>
                </div>

                <div class="mt-5 flex flex-wrap gap-2">
                    @foreach (['BCA', 'Mandiri', 'BRI', 'BNI', 'CIMB', 'GoPay', 'OVO', 'DANA', 'ShopeePay', 'LinkAja'] as $channel)
                        <span class="rounded-full bg-slate-50 px-3 py-1 text-xs font-semibold text-slate-600 ring-1 ring-slate-200">{{ $channel }}</span>
                    @endforeach
                </div>

                <div class="mt-5 border-t border-slate-100 pt-4 text-xs leading-5 text-slate-500">
                    Pilih nominal, scan QR, saldo masuk otomatis setelah pembayaran terdeteksi.
                </div>
            </section>

            <section class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-100 px-5 py-4">
                    <h2 class="font-bold text-slate-950">Riwayat Top Up</h2>
                </div>

                <div class="divide-y divide-slate-100">
                    @forelse ($recentInvoices as $recentInvoice)
                        <a href="{{ route('topup.show', $recentInvoice) }}" class="flex items-center justify-between gap-4 px-5 py-4 hover:bg-slate-50">
                            <div>
                                <div class="font-bold text-slate-950">{{ $money($recentInvoice->amount) }}</div>
                                <div class="mt-1 text-xs text-slate-500">{{ $recentInvoice->payment_method === 'qris2' ? 'QRIS 2' : 'QRIS 1' }} - {{ $recentInvoice->created_at->diffForHumans() }}</div>
                            </div>
                            <span class="rounded-full px-2.5 py-1 text-xs font-bold ring-1 {{ $recentInvoice->status === 'paid' ? 'bg-emerald-50 text-emerald-700 ring-emerald-200' : ($recentInvoice->status === 'pending' ? 'bg-amber-50 text-amber-800 ring-amber-200' : 'bg-slate-50 text-slate-600 ring-slate-200') }}">
                                {{ ucfirst($recentInvoice->status) }}
                            </span>
                        </a>
                    @empty
                        <div class="px-5 py-6 text-sm text-slate-500">Belum ada riwayat top up.</div>
                    @endforelse
                </div>
            </section>
        </aside>
    </div>

    @if ($isPending)
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
