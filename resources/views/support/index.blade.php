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
                <p class="text-sm font-semibold text-emerald-700">Support</p>
                <h1 class="text-2xl font-bold text-slate-950">Ticket support</h1>
            </div>
            <a href="{{ route('support.create') }}" class="inline-flex rounded-lg bg-emerald-600 px-4 py-2 text-sm font-bold text-white hover:bg-emerald-500">Buat Ticket</a>
        </div>
    </x-slot>

    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        @if (session('status'))
            <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 p-4 text-sm font-semibold text-emerald-800">{{ session('status') }}</div>
        @endif

        <section class="rounded-lg border border-slate-200 bg-white">
            <div class="border-b border-slate-100 px-5 py-4">
                <h2 class="font-bold text-slate-950">Semua ticket</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-slate-50 text-left text-xs font-bold uppercase text-slate-500">
                        <tr>
                            <th class="px-5 py-3">Ticket</th>
                            <th class="px-5 py-3">Kategori</th>
                            <th class="px-5 py-3">Status</th>
                            <th class="px-5 py-3">Update</th>
                            <th class="px-5 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($tickets as $ticket)
                            <tr class="hover:bg-slate-50">
                                <td class="px-5 py-4">
                                    <div class="font-semibold text-slate-950">{{ $ticket->ticket_no }}</div>
                                    <div class="mt-1 text-xs text-slate-500">{{ $ticket->subject }}</div>
                                </td>
                                <td class="px-5 py-4 font-semibold text-slate-700">{{ ucfirst($ticket->category) }}</td>
                                <td class="px-5 py-4">
                                    <span class="rounded-full px-2.5 py-1 text-xs font-bold ring-1 {{ $statusClass($ticket->status) }}">{{ ucfirst(str_replace('_', ' ', $ticket->status)) }}</span>
                                </td>
                                <td class="whitespace-nowrap px-5 py-4 text-slate-500">{{ ($ticket->last_replied_at ?? $ticket->updated_at)->format('d M Y H:i') }}</td>
                                <td class="px-5 py-4 text-right">
                                    <a href="{{ route('support.show', $ticket) }}" class="font-bold text-emerald-700 hover:text-emerald-600">Detail</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-5 py-12 text-center text-slate-500">Belum ada ticket support.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="border-t border-slate-100 px-5 py-4">{{ $tickets->links() }}</div>
        </section>
    </div>
</x-app-layout>
