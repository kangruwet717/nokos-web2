@php
    $statusClass = fn (string $status) => match ($status) {
        'open' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
        'pending_user' => 'bg-sky-50 text-sky-700 ring-sky-200',
        'closed' => 'bg-slate-100 text-slate-700 ring-slate-200',
        default => 'bg-amber-50 text-amber-800 ring-amber-200',
    };
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-sm font-semibold text-emerald-700">Support ticket</p>
                <h1 class="break-all text-2xl font-bold text-slate-950">{{ $ticket->ticket_no }}</h1>
            </div>
            <a href="{{ route('support.index') }}" class="inline-flex rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-bold text-slate-800 hover:bg-slate-50">Kembali</a>
        </div>
    </x-slot>

    <div class="mx-auto grid max-w-6xl gap-6 px-4 sm:px-6 lg:grid-cols-[1fr_0.45fr] lg:px-8">
        <section class="space-y-6">
            @if (session('status'))
                <div class="rounded-lg border border-emerald-200 bg-emerald-50 p-4 text-sm font-semibold text-emerald-800">{{ session('status') }}</div>
            @endif

            <div class="rounded-lg border border-slate-200 bg-white">
                <div class="border-b border-slate-100 px-5 py-4">
                    <h2 class="font-bold text-slate-950">{{ $ticket->subject }}</h2>
                    <div class="mt-2 flex flex-wrap gap-2 text-xs font-semibold text-slate-500">
                        <span>{{ ucfirst($ticket->category) }}</span>
                        <span>{{ $ticket->created_at->format('d M Y H:i') }}</span>
                    </div>
                </div>

                <div class="space-y-4 p-5">
                    @foreach ($ticket->messages as $message)
                        <div class="rounded-lg border {{ $message->is_admin ? 'border-emerald-200 bg-emerald-50' : 'border-slate-200 bg-white' }} p-4">
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <div class="font-bold text-slate-950">{{ $message->is_admin ? 'Admin Support' : ($message->user?->name ?? 'User') }}</div>
                                <div class="text-xs font-semibold text-slate-500">{{ $message->created_at->format('d M Y H:i') }}</div>
                            </div>
                            <div class="mt-3 whitespace-pre-line text-sm leading-6 text-slate-700">{{ $message->message }}</div>
                        </div>
                    @endforeach
                </div>
            </div>

            @if (! $ticket->isClosed())
                <section class="rounded-lg border border-slate-200 bg-white p-5">
                    <h2 class="font-bold text-slate-950">Balas ticket</h2>
                    <form method="POST" action="{{ route('support.reply', $ticket) }}" class="mt-4 space-y-4">
                        @csrf
                        <textarea name="message" rows="5" class="block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500" required>{{ old('message') }}</textarea>
                        <x-input-error :messages="$errors->get('message')" class="mt-2" />
                        <button class="rounded-lg bg-emerald-600 px-5 py-2.5 text-sm font-bold text-white hover:bg-emerald-500">Kirim Balasan</button>
                    </form>
                </section>
            @endif
        </section>

        <aside class="space-y-6">
            <section class="rounded-lg border border-slate-200 bg-white p-5">
                <h2 class="font-bold text-slate-950">Status</h2>
                <div class="mt-4">
                    <span class="rounded-full px-2.5 py-1 text-xs font-bold ring-1 {{ $statusClass($ticket->status) }}">{{ ucfirst(str_replace('_', ' ', $ticket->status)) }}</span>
                </div>

                @if ($ticket->otpOrder)
                    <div class="mt-5">
                        <div class="text-sm font-semibold text-slate-500">Order</div>
                        <a href="{{ route('otp.orders.show', $ticket->otpOrder) }}" class="mt-1 inline-block break-all font-bold text-emerald-700">{{ $ticket->otpOrder->order_no }}</a>
                    </div>
                @endif

                @if ($ticket->paymentInvoice)
                    <div class="mt-5">
                        <div class="text-sm font-semibold text-slate-500">Invoice</div>
                        <a href="{{ route('topup.show', $ticket->paymentInvoice) }}" class="mt-1 inline-block break-all font-bold text-emerald-700">{{ $ticket->paymentInvoice->invoice_no }}</a>
                    </div>
                @endif

                @if (! $ticket->isClosed())
                    <form method="POST" action="{{ route('support.close', $ticket) }}" class="mt-5">
                        @csrf
                        <button class="w-full rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-bold text-slate-800 hover:bg-slate-50">Tutup Ticket</button>
                    </form>
                @endif
            </section>
        </aside>
    </div>
</x-app-layout>
