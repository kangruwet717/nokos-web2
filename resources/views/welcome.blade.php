<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ config('app.name', 'Nokos') }}</title>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-stone-50 font-sans text-slate-950">
        <header class="border-b border-slate-200 bg-white">
            <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-4 sm:px-6 lg:px-8">
                <a href="{{ url('/') }}" class="flex items-center gap-3">
                    <span class="grid h-10 w-10 place-items-center rounded-lg bg-emerald-600 text-sm font-bold text-white">NX</span>
                    <span class="text-lg font-bold">Nokos</span>
                </a>

                <nav class="flex items-center gap-2">
                    @auth
                        <a href="{{ route('dashboard') }}" class="rounded-lg bg-slate-950 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800">Dashboard</a>
                    @else
                        <a href="{{ route('login') }}" class="rounded-lg px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100">Login</a>
                        <a href="{{ route('register') }}" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-500">Daftar</a>
                    @endauth
                </nav>
            </div>
        </header>

        <main>
            <section class="border-b border-slate-200 bg-white">
                <div class="mx-auto grid max-w-7xl gap-10 px-4 py-14 sm:px-6 lg:grid-cols-[1fr_0.9fr] lg:px-8 lg:py-20">
                    <div class="flex flex-col justify-center">
                        <p class="text-sm font-bold uppercase text-emerald-700">OTP virtual siap transaksi</p>
                        <h1 class="mt-4 max-w-3xl text-4xl font-extrabold leading-tight text-slate-950 sm:text-5xl">
                            Nokos untuk beli nomor OTP virtual dengan saldo wallet.
                        </h1>
                        <p class="mt-5 max-w-2xl text-base leading-7 text-slate-600">
                            Cari service, pilih negara, buat order, lalu pantau kode OTP dari satu dashboard. Fondasi pembayaran, wallet ledger, dan admin operasional sudah dipisahkan rapi.
                        </p>
                        <div class="mt-8 flex flex-wrap gap-3">
                            @auth
                                <a href="{{ route('otp.index') }}" class="rounded-lg bg-emerald-600 px-5 py-3 text-sm font-bold text-white hover:bg-emerald-500">Beli OTP</a>
                                <a href="{{ route('topup.index') }}" class="rounded-lg border border-slate-300 bg-white px-5 py-3 text-sm font-bold text-slate-800 hover:bg-slate-50">Top Up</a>
                            @else
                                <a href="{{ route('register') }}" class="rounded-lg bg-emerald-600 px-5 py-3 text-sm font-bold text-white hover:bg-emerald-500">Mulai Sekarang</a>
                                <a href="{{ route('login') }}" class="rounded-lg border border-slate-300 bg-white px-5 py-3 text-sm font-bold text-slate-800 hover:bg-slate-50">Masuk</a>
                            @endauth
                        </div>
                    </div>

                    <div class="rounded-lg border border-slate-200 bg-stone-50 p-4 shadow-sm">
                        <div class="rounded-lg border border-slate-200 bg-white p-5">
                            <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                                <div>
                                    <div class="text-sm font-bold text-slate-950">Marketplace OTP</div>
                                    <div class="mt-1 text-xs text-slate-500">Preview dashboard user</div>
                                </div>
                                <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-700">Active</span>
                            </div>
                            <div class="mt-5 grid gap-3 sm:grid-cols-2">
                                <div class="rounded-lg border border-slate-200 p-4">
                                    <div class="text-xs font-semibold text-slate-500">Saldo tersedia</div>
                                    <div class="mt-2 text-2xl font-bold text-slate-950">Rp250.000</div>
                                </div>
                                <div class="rounded-lg border border-slate-200 p-4">
                                    <div class="text-xs font-semibold text-slate-500">Order aktif</div>
                                    <div class="mt-2 text-2xl font-bold text-slate-950">2</div>
                                </div>
                            </div>
                            <div class="mt-5 space-y-3">
                                @foreach ([['WhatsApp', 'Indonesia', 'Rp4.500', 'Stok 128'], ['Telegram', 'Malaysia', 'Rp3.900', 'Stok 42'], ['Google', 'Philippines', 'Rp5.200', 'Stok 64']] as $item)
                                    <div class="flex items-center justify-between rounded-lg border border-slate-200 px-4 py-3">
                                        <div>
                                            <div class="text-sm font-bold text-slate-900">{{ $item[0] }}</div>
                                            <div class="text-xs text-slate-500">{{ $item[1] }} · {{ $item[3] }}</div>
                                        </div>
                                        <div class="text-right">
                                            <div class="text-sm font-bold text-slate-950">{{ $item[2] }}</div>
                                            <div class="text-xs font-semibold text-emerald-700">Siap beli</div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section class="mx-auto grid max-w-7xl gap-4 px-4 py-10 sm:grid-cols-3 sm:px-6 lg:px-8">
                <div class="rounded-lg border border-slate-200 bg-white p-5">
                    <div class="text-sm font-bold text-slate-950">Wallet ledger</div>
                    <p class="mt-2 text-sm leading-6 text-slate-600">Saldo tersedia dan saldo tertahan dicatat dengan transaksi before/after.</p>
                </div>
                <div class="rounded-lg border border-slate-200 bg-white p-5">
                    <div class="text-sm font-bold text-slate-950">Katalog provider</div>
                    <p class="mt-2 text-sm leading-6 text-slate-600">Service, negara, stok, dan harga disiapkan dari sinkronisasi SMSBower.</p>
                </div>
                <div class="rounded-lg border border-slate-200 bg-white p-5">
                    <div class="text-sm font-bold text-slate-950">Admin operasional</div>
                    <p class="mt-2 text-sm leading-6 text-slate-600">Suspend user, adjustment saldo, invoice, order, dan audit log tersedia di panel admin.</p>
                </div>
            </section>
        </main>

        <footer class="border-t border-slate-200 bg-white">
            <div class="mx-auto flex max-w-7xl flex-col gap-3 px-4 py-6 text-sm text-slate-500 sm:flex-row sm:items-center sm:justify-between sm:px-6 lg:px-8">
                <div>Gunakan layanan hanya untuk kebutuhan verifikasi yang sah dan sesuai aturan platform terkait.</div>
                <div class="flex flex-wrap gap-4 font-semibold">
                    <a href="{{ route('legal.terms') }}" class="hover:text-emerald-700">Terms</a>
                    <a href="{{ route('legal.privacy') }}" class="hover:text-emerald-700">Privacy</a>
                    <a href="{{ route('legal.refund') }}" class="hover:text-emerald-700">Refund</a>
                    <a href="{{ route('legal.acceptable-use') }}" class="hover:text-emerald-700">Acceptable Use</a>
                    <a href="{{ route('legal.contact') }}" class="hover:text-emerald-700">Contact</a>
                </div>
            </div>
        </footer>
    </body>
</html>
