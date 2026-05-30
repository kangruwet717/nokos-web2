<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm font-semibold text-emerald-700">Admin Wallet</p>
                <h1 class="text-2xl font-bold text-slate-950">Adjust balance</h1>
            </div>

            <a
                href="{{ route('filament.admin.resources.users.index') }}"
                class="inline-flex items-center justify-center rounded-md border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50"
            >
                Back to users
            </a>
        </div>
    </x-slot>

    <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
        <div class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
            <div class="mb-6 grid gap-4 sm:grid-cols-2">
                <div>
                    <p class="text-sm text-slate-500">User</p>
                    <p class="mt-1 font-semibold text-slate-950">{{ $user->name }}</p>
                    <p class="text-sm text-slate-600">{{ $user->email }}</p>
                </div>

                <div>
                    <p class="text-sm text-slate-500">Current balance</p>
                    <p class="mt-1 text-2xl font-bold text-slate-950">Rp{{ number_format((float) $user->balance, 0, ',', '.') }}</p>
                    <p class="text-sm text-slate-600">Reserved: Rp{{ number_format((float) $user->reserved_balance, 0, ',', '.') }}</p>
                </div>
            </div>

            <form method="POST" action="{{ route('admin.wallet-adjustments.update', $user) }}" class="space-y-5">
                @csrf

                <div>
                    <x-input-label for="amount" value="Amount" />
                    <x-text-input
                        id="amount"
                        name="amount"
                        type="number"
                        step="0.01"
                        class="mt-1 block w-full"
                        placeholder="50000 or -10000"
                        value="{{ old('amount') }}"
                        required
                    />
                    <p class="mt-2 text-sm text-slate-500">Use a negative value to debit the wallet.</p>
                    <x-input-error class="mt-2" :messages="$errors->get('amount')" />
                </div>

                <div>
                    <x-input-label for="reason" value="Reason" />
                    <textarea
                        id="reason"
                        name="reason"
                        rows="4"
                        class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500"
                        required
                    >{{ old('reason') }}</textarea>
                    <x-input-error class="mt-2" :messages="$errors->get('reason')" />
                </div>

                <div class="flex items-center justify-end gap-3">
                    <a
                        href="{{ route('filament.admin.resources.users.index') }}"
                        class="inline-flex items-center justify-center rounded-md border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50"
                    >
                        Cancel
                    </a>

                    <x-primary-button>
                        Save adjustment
                    </x-primary-button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
