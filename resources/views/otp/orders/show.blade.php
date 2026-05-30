@php
    $money = fn ($amount) => 'Rp'.number_format((float) $amount, 0, ',', '.');
    $statusClass = match ($order->status) {
        'completed' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
        'pending', 'waiting_sms' => 'bg-amber-50 text-amber-800 ring-amber-200',
        'cancelled', 'failed', 'expired', 'refunded' => 'bg-rose-50 text-rose-700 ring-rose-200',
        default => 'bg-slate-50 text-slate-700 ring-slate-200',
    };
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-sm font-semibold text-emerald-700">Detail order OTP</p>
                <h1 class="break-all text-2xl font-bold text-slate-950">{{ $order->order_no }}</h1>
            </div>
            <a href="{{ route('otp.orders.index') }}" class="inline-flex rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-bold text-slate-800 hover:bg-slate-50">Kembali</a>
        </div>
    </x-slot>

    <div class="mx-auto grid max-w-7xl gap-6 px-4 sm:px-6 lg:grid-cols-[1fr_0.7fr] lg:px-8">
        <section class="rounded-lg border border-slate-200 bg-white">
            <div class="flex flex-col gap-3 border-b border-slate-100 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="font-bold text-slate-950">{{ $order->otpService->name }} · {{ $order->country->name }}</h2>
                    <p class="mt-1 text-sm text-slate-500">Harga {{ $money($order->selling_price) }} · Kadaluarsa {{ $order->expires_at?->format('d M Y H:i') ?? '-' }}</p>
                </div>
                <span class="w-fit rounded-full px-2.5 py-1 text-xs font-bold ring-1 {{ $statusClass }}">{{ ucfirst(str_replace('_', ' ', $order->status)) }}</span>
            </div>

            <div class="grid gap-4 p-5 sm:grid-cols-2">
                <div class="rounded-lg bg-slate-50 p-4" x-data="{ copied: false }">
                    <div class="text-sm font-semibold text-slate-500">Nomor</div>
                    <div class="mt-2 break-all text-2xl font-bold text-slate-950">{{ $order->phone_number ?? '-' }}</div>
                    @if ($order->phone_number)
                        <button type="button" @click="navigator.clipboard.writeText(@js($order->phone_number)); copied = true; setTimeout(() => copied = false, 1500)" class="mt-3 rounded-lg border border-slate-300 bg-white px-3 py-2 text-xs font-bold text-slate-700 hover:bg-slate-50">
                            <span x-show="!copied">Salin nomor</span>
                            <span x-show="copied">Tersalin</span>
                        </button>
                    @endif
                </div>

                <div class="rounded-lg bg-emerald-50 p-4" x-data="{ copied: false }">
                    <div class="text-sm font-semibold text-emerald-800">Kode OTP</div>
                    <div class="mt-2 break-all text-3xl font-extrabold text-emerald-950">{{ $order->sms_code ?? '-' }}</div>
                    @if ($order->sms_code)
                        <button type="button" @click="navigator.clipboard.writeText(@js($order->sms_code)); copied = true; setTimeout(() => copied = false, 1500)" class="mt-3 rounded-lg border border-emerald-200 bg-white px-3 py-2 text-xs font-bold text-emerald-800 hover:bg-emerald-50">
                            <span x-show="!copied">Salin kode</span>
                            <span x-show="copied">Tersalin</span>
                        </button>
                    @endif
                </div>

                <div>
                    <div class="text-sm font-semibold text-slate-500">Provider activation</div>
                    <div class="mt-1 break-all font-bold text-slate-950">{{ $order->provider_activation_id ?? '-' }}</div>
                </div>
                <div>
                    <div class="text-sm font-semibold text-slate-500">Dibuat</div>
                    <div class="mt-1 font-bold text-slate-950">{{ $order->created_at->format('d M Y H:i') }}</div>
                </div>
                <div>
                    <div class="text-sm font-semibold text-slate-500">Selesai</div>
                    <div class="mt-1 font-bold text-slate-950">{{ $order->completed_at?->format('d M Y H:i') ?? '-' }}</div>
                </div>
                <div>
                    <div class="text-sm font-semibold text-slate-500">Dibatalkan</div>
                    <div class="mt-1 font-bold text-slate-950">{{ $order->cancelled_at?->format('d M Y H:i') ?? '-' }}</div>
                </div>
            </div>

            <div class="flex flex-wrap gap-3 border-t border-slate-100 p-5">
                <form method="POST" action="{{ route('otp.orders.refresh', $order) }}">
                    @csrf
                    <button class="rounded-lg bg-slate-950 px-4 py-2.5 text-sm font-bold text-white hover:bg-slate-800">Cek Status</button>
                </form>

                @if ($order->canBeCancelled())
                    <form method="POST" action="{{ route('otp.orders.cancel', $order) }}">
                        @csrf
                        <button class="rounded-lg border border-rose-200 bg-rose-50 px-4 py-2.5 text-sm font-bold text-rose-700 hover:bg-rose-100">Batalkan</button>
                    </form>
                @endif
            </div>
        </section>

        <aside class="rounded-lg border border-slate-200 bg-white">
            <div class="border-b border-slate-100 px-5 py-4">
                <h2 class="font-bold text-slate-950">Timeline status</h2>
            </div>
            <div class="divide-y divide-slate-100">
                @forelse ($order->statusLogs as $log)
                    <div class="px-5 py-4">
                        <div class="flex items-start justify-between gap-3">
                            <div class="font-bold text-slate-950">{{ ucfirst(str_replace('_', ' ', $log->new_status)) }}</div>
                            <div class="whitespace-nowrap text-xs text-slate-500">{{ $log->created_at->format('H:i') }}</div>
                        </div>
                        <div class="mt-1 text-sm text-slate-500">{{ $log->created_at->format('d M Y') }} · {{ ucfirst($log->source) }}</div>
                        @if ($log->message)
                            <p class="mt-2 text-sm leading-6 text-slate-600">{{ $log->message }}</p>
                        @endif
                    </div>
                @empty
                    <div class="px-5 py-10 text-center text-sm text-slate-500">Belum ada log status.</div>
                @endforelse
            </div>
        </aside>
    </div>

    @if ($order->status === 'waiting_sms')
        <script>
            window.setInterval(async () => {
                try {
                    const response = await fetch(@json(route('otp.orders.status', $order)), {
                        headers: { Accept: 'application/json' },
                    });
                    const payload = await response.json();

                    if (payload.status && payload.status !== 'waiting_sms') {
                        window.location.reload();
                    }
                } catch (error) {
                    //
                }
            }, @json(config('services.smsbower.status_poll_interval_ms', 5000)));
        </script>
    @endif
</x-app-layout>
