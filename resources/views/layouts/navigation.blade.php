@php
    $user = Auth::user();
    $balance = $user ? number_format((float) $user->availableBalance(), 0, ',', '.') : '0';
@endphp

<nav
    x-data="{
        open: false,
        darkMode: document.documentElement.classList.contains('dark'),
        toggleTheme() {
            this.darkMode = ! this.darkMode;
            document.documentElement.classList.toggle('dark', this.darkMode);
            localStorage.setItem('blueline-theme', this.darkMode ? 'dark' : 'light');
        }
    }"
    class="sticky top-0 z-40 border-b border-slate-200 bg-white/95 backdrop-blur dark:border-slate-800 dark:bg-slate-900/95"
>
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex h-16 items-center justify-between gap-4">
            <div class="flex min-w-0 items-center gap-6">
                <a href="{{ route('dashboard') }}" class="flex shrink-0 items-center gap-3">
                    <img src="{{ asset('images/logo.png') }}" alt="{{ config('app.name', 'Blueline OTP') }} logo" class="h-10 w-auto max-w-[150px] object-contain">
                </a>

                <div class="hidden items-center gap-1 md:flex">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                        Dashboard
                    </x-nav-link>
                    <x-nav-link :href="route('otp.index')" :active="request()->routeIs('otp.index')">
                        Beli OTP
                    </x-nav-link>
                    <x-nav-link :href="route('otp.orders.index')" :active="request()->routeIs('otp.orders.*')">
                        Order
                    </x-nav-link>
                    <x-nav-link :href="route('topup.index')" :active="request()->routeIs('topup.*')">
                        Top Up
                    </x-nav-link>
                    <x-nav-link :href="route('wallet.history')" :active="request()->routeIs('wallet.history')">
                        Wallet
                    </x-nav-link>
                    <x-nav-link :href="route('support.index')" :active="request()->routeIs('support.*')">
                        Support
                    </x-nav-link>
                </div>
            </div>

            <div class="hidden items-center gap-3 sm:flex">
                <button
                    type="button"
                    @click="toggleTheme()"
                    class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-600 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 dark:hover:bg-slate-800"
                    :aria-label="darkMode ? 'Gunakan tema terang' : 'Gunakan tema gelap'"
                    :title="darkMode ? 'Tema terang' : 'Tema gelap'"
                >
                    <svg x-show="! darkMode" class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z" />
                    </svg>
                    <svg x-cloak x-show="darkMode" class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v2m0 14v2m9-9h-2M5 12H3m15.36 6.36l-1.42-1.42M7.06 7.06 5.64 5.64m12.72 0-1.42 1.42M7.06 16.94l-1.42 1.42M12 8a4 4 0 100 8 4 4 0 000-8z" />
                    </svg>
                </button>

                <a href="{{ route('topup.index') }}" class="rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm font-semibold text-emerald-800 dark:border-emerald-900/70 dark:bg-emerald-950 dark:text-emerald-200">
                    Rp{{ $balance }}
                </a>

                @if ($user?->isAdmin())
                    <a href="/admin" class="rounded-lg border border-slate-200 px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800">Admin</a>
                    <a href="{{ route('admin.reports.index') }}" class="rounded-lg border border-slate-200 px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800">Reports</a>
                @endif

                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800">
                            <span class="max-w-32 truncate">{{ $user->name }}</span>
                            <svg class="h-4 w-4 text-slate-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.17l3.71-3.94a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">Profile</x-dropdown-link>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-dropdown-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">
                                Log Out
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <div class="flex items-center gap-2 md:hidden">
                <button
                    type="button"
                    @click="toggleTheme()"
                    class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-slate-200 text-slate-600 dark:border-slate-700 dark:text-slate-300"
                    :aria-label="darkMode ? 'Gunakan tema terang' : 'Gunakan tema gelap'"
                >
                    <svg x-show="! darkMode" class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z" />
                    </svg>
                    <svg x-cloak x-show="darkMode" class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v2m0 14v2m9-9h-2M5 12H3m15.36 6.36l-1.42-1.42M7.06 7.06 5.64 5.64m12.72 0-1.42 1.42M7.06 16.94l-1.42 1.42M12 8a4 4 0 100 8 4 4 0 000-8z" />
                    </svg>
                </button>

                <button @click="open = ! open" class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-slate-200 text-slate-600 dark:border-slate-700 dark:text-slate-300">
                <svg class="h-5 w-5" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                    <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7h16M4 12h16M4 17h16" />
                    <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
                </button>
            </div>
        </div>
    </div>

    <div :class="{'block': open, 'hidden': ! open}" class="hidden border-t border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900 md:hidden">
        <div class="space-y-1 px-4 py-3">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">Dashboard</x-responsive-nav-link>
            <x-responsive-nav-link :href="route('otp.index')" :active="request()->routeIs('otp.index')">Beli OTP</x-responsive-nav-link>
            <x-responsive-nav-link :href="route('otp.orders.index')" :active="request()->routeIs('otp.orders.*')">Order</x-responsive-nav-link>
            <x-responsive-nav-link :href="route('topup.index')" :active="request()->routeIs('topup.*')">Top Up</x-responsive-nav-link>
            <x-responsive-nav-link :href="route('wallet.history')" :active="request()->routeIs('wallet.history')">Wallet</x-responsive-nav-link>
            <x-responsive-nav-link :href="route('support.index')" :active="request()->routeIs('support.*')">Support</x-responsive-nav-link>
        </div>

        <div class="border-t border-slate-200 px-4 py-4 dark:border-slate-800">
            <div class="text-sm font-semibold text-slate-950 dark:text-slate-100">{{ $user->name }}</div>
            <div class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ $user->email }}</div>
            <div class="mt-3 rounded-lg bg-emerald-50 px-3 py-2 text-sm font-semibold text-emerald-800 dark:bg-emerald-950 dark:text-emerald-200">Saldo Rp{{ $balance }}</div>
            <div class="mt-3 space-y-1">
                @if ($user?->isAdmin())
                    <x-responsive-nav-link href="/admin">Admin</x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('admin.reports.index')">Reports</x-responsive-nav-link>
                @endif
                <x-responsive-nav-link :href="route('profile.edit')">Profile</x-responsive-nav-link>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <x-responsive-nav-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">
                        Log Out
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>
