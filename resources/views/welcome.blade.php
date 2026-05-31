<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ config('app.name', 'Blueline OTP') }} - Layanan Nomor OTP Virtual untuk Verifikasi Online</title>
        <meta name="description" content="Blueline OTP menyediakan nomor OTP virtual untuk kebutuhan verifikasi dan testing yang sah dengan harga transparan, riwayat transaksi jelas, banyak layanan, dan banyak negara.">
        <link rel="icon" href="{{ asset('images/logo.png') }}">
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800,900&display=swap" rel="stylesheet" />
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-white font-sans text-slate-950 antialiased">
        <header class="sticky top-0 z-50 border-b border-slate-200 bg-white/95 backdrop-blur">
            <div class="mx-auto flex h-16 max-w-7xl items-center justify-between gap-4 px-4 sm:px-6 lg:px-8">
                <a href="{{ url('/') }}" class="flex shrink-0 items-center gap-3">
                    <img src="{{ asset('images/logo.png') }}" alt="{{ config('app.name', 'Blueline OTP') }} logo" class="h-10 w-auto max-w-[155px] object-contain">
                </a>

                <nav class="hidden items-center gap-5 text-sm font-semibold text-slate-600 lg:flex">
                    <a href="#fitur" class="hover:text-emerald-700">Fitur</a>
                    <a href="#layanan" class="hover:text-emerald-700">Layanan</a>
                    <a href="#harga" class="hover:text-emerald-700">Harga</a>
                    <a href="#negara" class="hover:text-emerald-700">Negara</a>
                    <a href="#api" class="hover:text-emerald-700">API</a>
                    <a href="#faq" class="hover:text-emerald-700">FAQ</a>
                </nav>

                <div class="flex shrink-0 items-center gap-2">
                    @auth
                        <a href="{{ route('dashboard') }}" class="rounded-md border border-slate-300 bg-white px-3 py-2 text-sm font-bold text-slate-800 hover:bg-slate-50">Dashboard</a>
                        <a href="{{ route('otp.index') }}" class="rounded-md bg-emerald-600 px-4 py-2 text-sm font-bold text-white hover:bg-emerald-500">Beli OTP</a>
                    @else
                        <a href="{{ route('login') }}" class="rounded-md px-3 py-2 text-sm font-bold text-slate-700 hover:bg-slate-100">Login</a>
                        <a href="{{ route('register') }}" class="rounded-md bg-emerald-600 px-4 py-2 text-sm font-bold text-white hover:bg-emerald-500">Daftar</a>
                    @endauth
                </div>
            </div>
        </header>

        <main>
            <section class="relative overflow-hidden border-b border-slate-200 bg-slate-950">
                <div class="absolute inset-0 opacity-20" style="background-image: linear-gradient(rgba(255,255,255,.08) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,.08) 1px, transparent 1px); background-size: 42px 42px;"></div>
                <div class="relative mx-auto grid max-w-7xl items-center gap-10 px-4 py-14 sm:px-6 sm:py-16 lg:min-h-[680px] lg:grid-cols-[0.9fr_1.1fr] lg:px-8">
                    <div>
                        <p class="text-sm font-black uppercase text-emerald-300">Blueline OTP</p>
                        <h1 class="mt-4 max-w-3xl text-4xl font-black leading-tight text-white sm:text-5xl">
                            Layanan Nomor OTP Virtual untuk Verifikasi Online
                        </h1>
                        <p class="mt-5 max-w-2xl text-base leading-8 text-slate-300 sm:text-lg">
                            Dapatkan nomor virtual untuk menerima kode OTP dari berbagai platform populer. Pilih layanan, negara, cek harga, dan pantau status OTP langsung dari dashboard Blueline.
                        </p>

                        <div class="mt-6 flex flex-wrap gap-2 text-sm font-bold text-slate-200">
                            @foreach (['WhatsApp', 'Telegram', 'Google', 'Instagram', 'TikTok', '100+ layanan lainnya'] as $service)
                                <span>{{ $service }}</span>
                                @unless ($loop->last)
                                    <span class="text-emerald-400">&bull;</span>
                                @endunless
                            @endforeach
                        </div>

                        <div class="mt-6 grid max-w-2xl gap-3 sm:grid-cols-3">
                            @foreach ([
                                ['Order cepat', 'Pilih layanan, negara, lalu pantau nomor dari dashboard.'],
                                ['Harga terlihat', 'Harga final tampil sebelum order dibuat.'],
                                ['Support tersedia', 'Buat tiket jika pembayaran atau order perlu dicek.'],
                            ] as $trust)
                                <div class="rounded-lg border border-white/10 bg-white/5 p-4">
                                    <div class="text-sm font-black text-white">{{ $trust[0] }}</div>
                                    <div class="mt-2 text-xs leading-5 text-slate-400">{{ $trust[1] }}</div>
                                </div>
                            @endforeach
                        </div>

                        <div class="mt-8 flex flex-wrap gap-3">
                            <a href="{{ auth()->check() ? route('otp.index') : route('register') }}" class="rounded-md bg-emerald-500 px-5 py-3 text-sm font-black text-white shadow-sm shadow-emerald-950/40 hover:bg-emerald-400">{{ auth()->check() ? 'Beli OTP' : 'Daftar' }}</a>
                            <a href="#harga" class="rounded-md border border-white/20 bg-white/10 px-5 py-3 text-sm font-black text-white hover:bg-white/15">Lihat Harga</a>
                        </div>

                        <div class="mt-10 grid max-w-xl grid-cols-3 gap-3 rounded-lg border border-white/10 bg-white/5 p-4">
                            <div class="border-l border-emerald-300/40 pl-4">
                                <div class="text-2xl font-black text-white">100+</div>
                                <div class="mt-1 text-xs font-semibold text-slate-400">Layanan populer</div>
                            </div>
                            <div class="border-l border-emerald-300/40 pl-4">
                                <div class="text-2xl font-black text-white">70+</div>
                                <div class="mt-1 text-xs font-semibold text-slate-400">Negara tersedia</div>
                            </div>
                            <div class="border-l border-emerald-300/40 pl-4">
                                <div class="text-2xl font-black text-white">24/7</div>
                                <div class="mt-1 text-xs font-semibold text-slate-400">Dashboard aktif</div>
                            </div>
                        </div>
                    </div>

                    <div class="relative">
                        <div class="rounded-lg border border-white/15 bg-white/10 p-4 shadow-2xl shadow-black/30 backdrop-blur">
                            <div class="grid gap-3">
                                @foreach ([
                                    ['WhatsApp', '+62 812-xxxx-xxxx', 'Kode verifikasi: 123-456', 'SMS diterima'],
                                    ['Telegram', '+60 13-xxxx-xxxx', 'Kode OTP: 78945', 'SMS diterima'],
                                    ['Google', '+63 9xx-xxx-xxxx', 'Kode: G-274895', 'SMS diterima'],
                                ] as $sms)
                                    <article class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
                                        <div class="flex items-center justify-between gap-3">
                                            <div class="font-black text-slate-950">{{ $sms[0] }}</div>
                                            <span class="rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-bold text-emerald-700">{{ $sms[3] }}</span>
                                        </div>
                                        <div class="mt-2 text-sm font-bold text-slate-600">{{ $sms[1] }}</div>
                                        <div class="mt-3 rounded-md bg-slate-50 px-3 py-2 text-sm font-black text-slate-950">{{ $sms[2] }}</div>
                                    </article>
                                @endforeach
                            </div>

                            <div class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-4">
                                @foreach ([['100+ Layanan', 'Populer'], ['70+ Negara', 'Tersedia'], ['Status order', 'Real-time'], ['Riwayat transaksi', 'Tercatat']] as $stat)
                                    <div class="min-h-20 rounded-md bg-white/10 p-3 text-white ring-1 ring-white/10">
                                        <div class="text-xs font-semibold text-slate-300">{{ $stat[0] }}</div>
                                        <div class="mt-1 text-lg font-black">{{ $stat[1] }}</div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section class="border-b border-slate-200 bg-white py-10">
                <div class="mx-auto flex max-w-7xl flex-wrap items-center justify-center gap-3 px-4 sm:px-6 lg:px-8">
                    @foreach (['WhatsApp', 'Telegram', 'Instagram', 'Google', 'TikTok', 'Facebook', 'Discord', 'Shopee', 'Tokopedia', 'Gojek'] as $service)
                        <span class="rounded-full border border-slate-200 bg-slate-50 px-4 py-2 text-sm font-black text-slate-700">{{ $service }}</span>
                    @endforeach
                </div>
            </section>

            <section id="fitur" class="scroll-mt-24 border-b border-slate-200 bg-white pb-24 pt-20 sm:pb-28 sm:pt-24">
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div class="max-w-2xl">
                        <p class="text-sm font-black uppercase text-emerald-700">Kenapa Pilih Blueline OTP?</p>
                        <h2 class="mt-3 text-3xl font-black text-slate-950 sm:text-4xl">Cepat, transparan, dan mudah digunakan untuk kebutuhan verifikasi yang sah.</h2>
                    </div>

                    <div class="mt-10 grid gap-5 md:grid-cols-2 lg:grid-cols-3">
                        @foreach ([
                            ['Proses Cepat', 'Pesan nomor virtual dan pantau OTP langsung dari dashboard.'],
                            ['Harga Transparan', 'Lihat harga, stok, negara, dan service sebelum membuat order.'],
                            ['Riwayat Jelas', 'Top up, order, saldo tertahan, dan refund tercatat rapi.'],
                            ['Banyak Service', 'Mendukung berbagai platform populer untuk kebutuhan verifikasi.'],
                            ['Banyak Negara', 'Pilih nomor dari berbagai negara sesuai kebutuhan testing.'],
                            ['Siap Dikembangkan', 'Cocok untuk pengembangan bot Telegram dan integrasi H2H.'],
                        ] as $feature)
                            <article class="min-h-48 rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
                                <div class="flex h-10 w-10 items-center justify-center rounded-md bg-emerald-50 text-sm font-black text-emerald-700 ring-1 ring-emerald-100">{{ $loop->iteration }}</div>
                                <h3 class="mt-5 text-lg font-black text-slate-950">{{ $feature[0] }}</h3>
                                <p class="mt-3 text-sm leading-6 text-slate-600">{{ $feature[1] }}</p>
                            </article>
                        @endforeach
                    </div>
                </div>
            </section>

            <section id="cara-kerja" class="scroll-mt-24 border-b border-slate-200 bg-slate-50 pb-24 pt-24 sm:pb-28 sm:pt-28">
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div class="grid gap-10 lg:grid-cols-[0.8fr_1.2fr] lg:items-start">
                        <div>
                            <p class="text-sm font-black uppercase text-emerald-700">Cara kerja</p>
                            <h2 class="mt-3 text-3xl font-black text-slate-950 sm:text-4xl">Empat langkah sampai kode OTP tampil.</h2>
                            <p class="mt-4 text-sm leading-7 text-slate-600">Alurnya dibuat jelas agar pengguna tidak bingung sejak top up sampai order selesai.</p>
                        </div>

                        <div class="grid gap-5 sm:grid-cols-2">
                            @foreach ([
                                ['Isi saldo', 'Buat top up, bayar sesuai invoice, lalu pantau status pembayaran.'],
                                ['Pilih layanan', 'Pilih platform dan negara yang memiliki stok aktif.'],
                                ['Buat order', 'Harga terlihat sebelum order, lalu saldo ditahan saat order berjalan.'],
                                ['Pantau OTP', 'Nomor, status, kode OTP, dan pembatalan tersedia di halaman order.'],
                            ] as $step)
                                <div class="min-h-40 rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
                                    <div class="text-4xl font-black text-emerald-600">{{ $loop->iteration }}</div>
                                    <h3 class="mt-4 text-lg font-black text-slate-950">{{ $step[0] }}</h3>
                                    <p class="mt-2 text-sm leading-6 text-slate-600">{{ $step[1] }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </section>

            <section id="layanan" class="scroll-mt-24 border-b border-slate-200 bg-white pb-24 pt-24 sm:pb-28 sm:pt-28">
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                        <div>
                            <p class="text-sm font-black uppercase text-emerald-700">Layanan Populer</p>
                            <h2 class="mt-3 text-3xl font-black text-slate-950 sm:text-4xl">100+ Layanan OTP Didukung</h2>
                            <p class="mt-3 max-w-2xl text-sm leading-7 text-slate-600">Pilih platform yang Anda butuhkan, cek stok dan harga secara real-time.</p>
                        </div>
                        <a href="{{ auth()->check() ? route('otp.index') : route('register') }}" class="w-fit rounded-md bg-emerald-600 px-4 py-2 text-sm font-black text-white hover:bg-emerald-500">Lihat Semua Layanan</a>
                    </div>

                    <div class="mt-10 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                        @foreach (['WhatsApp', 'Telegram', 'Google', 'Instagram', 'Facebook', 'TikTok', 'Discord', 'Shopee', 'Tokopedia', 'Gojek', 'Twitter/X', 'LinkedIn'] as $service)
                            <div class="min-h-24 rounded-lg border border-slate-200 bg-slate-50 p-4">
                                <div class="flex items-center justify-between gap-3">
                                    <div class="font-black text-slate-950">{{ $service }}</div>
                                    <span class="rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-bold text-emerald-700">OTP</span>
                                </div>
                                <div class="mt-2 text-xs font-semibold text-slate-500">Cek stok dan harga sebelum order.</div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>

            <section id="harga" class="scroll-mt-24 border-b border-slate-200 bg-slate-50 pb-24 pt-24 sm:pb-28 sm:pt-28">
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div class="grid gap-8 lg:grid-cols-[0.8fr_1.2fr] lg:items-start">
                        <div>
                            <p class="text-sm font-black uppercase text-emerald-700">Harga</p>
                            <h2 class="mt-3 text-3xl font-black text-slate-950 sm:text-4xl">Harga Transparan, Cek Sebelum Order</h2>
                            <p class="mt-4 text-sm leading-7 text-slate-600">Harga contoh ditampilkan agar Anda punya gambaran awal. Harga final selalu tampil sebelum order dibuat.</p>
                        </div>

                        <div class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
                            <div class="grid grid-cols-[1fr_1fr_auto_auto] gap-3 border-b border-slate-200 bg-slate-50 px-4 py-3 text-xs font-black uppercase text-slate-500">
                                <div>Service</div>
                                <div>Negara</div>
                                <div>Mulai dari</div>
                                <div>Status</div>
                            </div>
                            @foreach ([['WhatsApp', 'Indonesia', 'Rp4.500', 'Siap beli'], ['Telegram', 'Malaysia', 'Rp3.900', 'Siap beli'], ['Google', 'Philippines', 'Rp5.200', 'Siap beli']] as $price)
                                <div class="grid grid-cols-[1fr_1fr_auto_auto] items-center gap-3 border-b border-slate-100 px-4 py-4 text-sm last:border-b-0">
                                    <div class="font-bold text-slate-950">{{ $price[0] }}</div>
                                    <div class="font-semibold text-slate-600">{{ $price[1] }}</div>
                                    <div class="text-right font-black text-slate-950">{{ $price[2] }}</div>
                                    <div class="rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-bold text-emerald-700">{{ $price[3] }}</div>
                                </div>
                            @endforeach
                            <div class="border-t border-slate-200 bg-slate-50 px-4 py-3 text-xs font-semibold text-slate-500">
                                Harga final dan stok aktif selalu dicek kembali di halaman order sebelum pembelian dibuat.
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section id="negara" class="scroll-mt-24 border-b border-slate-200 bg-white pb-24 pt-24 sm:pb-28 sm:pt-28">
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                        <div>
                            <p class="text-sm font-black uppercase text-emerald-700">Negara</p>
                            <h2 class="mt-3 text-3xl font-black text-slate-950 sm:text-4xl">Nomor Virtual dari Berbagai Negara</h2>
                            <p class="mt-3 max-w-2xl text-sm leading-7 text-slate-600">Pilih negara sesuai kebutuhan verifikasi dan testing Anda.</p>
                        </div>
                        <a href="{{ auth()->check() ? route('otp.index') : route('register') }}" class="w-fit rounded-md border border-slate-300 bg-white px-4 py-2 text-sm font-black text-slate-800 hover:bg-slate-50">Lihat Semua Negara</a>
                    </div>

                    <div class="mt-10 flex flex-wrap gap-3">
                        @foreach (['Indonesia', 'Malaysia', 'Philippines', 'Singapore', 'Thailand', 'Vietnam', 'USA', 'India', 'Brazil', 'Turkey', 'Japan', 'Australia'] as $country)
                            <span class="rounded-full border border-slate-200 bg-slate-50 px-4 py-2 text-sm font-bold text-slate-700">{{ $country }}</span>
                        @endforeach
                    </div>
                </div>
            </section>

            <section class="border-b border-slate-200 bg-slate-950 pb-24 pt-24 text-white sm:pb-28 sm:pt-28">
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div>
                        <p class="text-sm font-black uppercase text-emerald-300">Testimoni pengguna</p>
                        <h2 class="mt-3 max-w-3xl text-3xl font-black sm:text-4xl">Dipakai untuk verifikasi yang cepat, jelas, dan mudah dipantau.</h2>
                        <p class="mt-4 max-w-2xl text-sm leading-7 text-slate-300">Pengguna memilih Blueline karena proses order sederhana, status mudah dibaca, dan riwayat transaksi tersimpan rapi.</p>
                    </div>

                    <div class="mt-10 grid gap-5 md:grid-cols-3">
                        @foreach ([
                            ['Raka Pratama', 'Owner toko digital', 'Biasanya saya cek stok dulu, pilih negara, lalu pantau status order dari dashboard. Alurnya lebih gampang dijelaskan ke tim.'],
                            ['Maya Salsabila', 'Admin operasional', 'Harga sudah kelihatan sebelum order, jadi lebih enak buat kontrol pemakaian saldo dan cek transaksi harian.'],
                            ['Dimas Arya', 'Developer', 'Dashboard-nya membantu untuk testing internal. Nomor, status, dan kode OTP bisa dicek tanpa bolak-balik halaman.'],
                        ] as $testimonial)
                            <div class="flex min-h-64 flex-col rounded-lg border border-white/10 bg-white/10 p-5 shadow-sm">
                                <div class="text-lg tracking-normal text-amber-300" aria-label="Rating 5 dari 5">&#9733;&#9733;&#9733;&#9733;&#9733;</div>
                                <p class="mt-4 text-sm leading-7 text-slate-100">"{{ $testimonial[2] }}"</p>
                                <div class="mt-auto border-t border-white/10 pt-4">
                                    <div class="font-black">{{ $testimonial[0] }}</div>
                                    <div class="mt-1 text-xs font-semibold text-slate-400">{{ $testimonial[1] }}</div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>

            <section id="api" class="scroll-mt-24 border-b border-slate-200 bg-white pb-24 pt-24 sm:pb-28 sm:pt-28">
                <div class="mx-auto grid max-w-7xl gap-8 px-4 sm:px-6 lg:grid-cols-[1fr_1fr] lg:items-center lg:px-8">
                    <div>
                        <p class="text-sm font-black uppercase text-emerald-700">Integrasi</p>
                        <h2 class="mt-3 text-3xl font-black text-slate-950 sm:text-4xl">Siap dikembangkan untuk bot Telegram dan kebutuhan H2H.</h2>
                        <p class="mt-4 text-sm leading-7 text-slate-600">Jika Anda membutuhkan order lewat bot atau sistem internal, Blueline bisa dikembangkan dengan alur API dan webhook yang mengikuti sistem wallet serta order yang sama.</p>
                        <div class="mt-5 grid gap-2 text-sm font-bold text-slate-700 sm:grid-cols-2">
                            @foreach (['Rencana REST API', 'Webhook callback', 'Order via bot', 'Riwayat order', 'Cek status SMS', 'Saldo wallet'] as $apiFeature)
                                <div class="rounded-md bg-emerald-50 px-3 py-2 text-emerald-800">{{ $apiFeature }}</div>
                            @endforeach
                        </div>
                        <a href="{{ auth()->check() ? route('support.create') : route('register') }}" class="mt-6 inline-flex rounded-md bg-emerald-600 px-5 py-3 text-sm font-black text-white hover:bg-emerald-500">Konsultasi Integrasi</a>
                    </div>

                    <div class="overflow-x-auto rounded-lg bg-slate-950 p-5 font-mono text-sm text-slate-100 shadow-sm">
                        <div class="text-emerald-300">POST /api/v1/orders</div>
                        <div class="mt-5 text-emerald-300">GET /api/v1/orders/ORDER_ID</div>
                        <div class="mt-5 text-slate-400">{</div>
                        <div class="pl-4">"status": "waiting_sms",</div>
                        <div class="pl-4">"number": "+62 812-xxxx-xxxx",</div>
                        <div class="pl-4">"code": null,</div>
                        <div class="pl-4">"wallet_balance": "shown_in_dashboard"</div>
                        <div class="text-slate-400">}</div>
                    </div>
                </div>
            </section>

            <section id="faq" class="scroll-mt-24 bg-slate-50 pb-24 pt-24 sm:pb-28 sm:pt-28">
                <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
                    <div class="text-center">
                        <p class="text-sm font-black uppercase text-emerald-700">FAQ</p>
                        <h2 class="mt-3 text-3xl font-black text-slate-950 sm:text-4xl">Pertanyaan yang sering muncul.</h2>
                    </div>

                    <div class="mt-10 divide-y divide-slate-200 rounded-lg border border-slate-200 bg-white">
                        @foreach ([
                            ['Untuk apa Blueline OTP?', 'Untuk kebutuhan testing, verifikasi, dan integrasi yang sah. Layanan wajib digunakan sesuai aturan platform dan hukum yang berlaku.'],
                            ['Apakah saldo kembali jika order gagal?', 'Jika order gagal atau pembatalan memenuhi syarat, saldo yang tertahan akan dikembalikan sesuai status order dan kebijakan refund.'],
                            ['Berapa lama OTP masuk?', 'Waktu OTP bergantung layanan dan jaringan tujuan. Anda bisa memantau status order langsung dari halaman order.'],
                            ['Apakah order bisa dibatalkan?', 'Order bisa dibatalkan selama status dan aturan pembatalannya masih memenuhi syarat. Tombol batal akan tampil jika order masih eligible.'],
                            ['Bagaimana jika pembayaran sudah berhasil tapi saldo belum masuk?', 'Buka halaman invoice dan gunakan cek pembayaran. Jika masih bermasalah, buat tiket support dengan menyertakan bukti pembayaran.'],
                            ['Bagaimana jika OTP tidak masuk?', 'Pantau halaman order terlebih dahulu. Jika belum ada kode, gunakan refresh status atau batalkan order jika tombol pembatalan tersedia.'],
                            ['Kenapa stok dan harga bisa berubah?', 'Stok nomor dan harga layanan diperbarui berkala di katalog Blueline. Karena permintaan bisa berubah cepat, jumlah stok dan harga dapat menyesuaikan sewaktu-waktu.'],
                            ['Apakah layanan ini menjamin semua OTP masuk?', 'Tidak semua SMS selalu berhasil masuk karena dipengaruhi jaringan dan platform tujuan. Karena itu status order, refresh, pembatalan, dan riwayat dibuat jelas agar kendala bisa ditangani.'],
                        ] as $faq)
                            <details class="group p-5">
                                <summary class="flex cursor-pointer list-none items-center justify-between gap-4 font-black text-slate-950">
                                    {{ $faq[0] }}
                                    <span class="text-emerald-700 group-open:rotate-45">+</span>
                                </summary>
                                <p class="mt-3 text-sm leading-7 text-slate-600">{{ $faq[1] }}</p>
                            </details>
                        @endforeach
                    </div>
                </div>
            </section>

            <section class="bg-slate-950 py-12 text-white">
                <div class="mx-auto flex max-w-7xl flex-col gap-6 px-4 sm:px-6 md:flex-row md:items-center md:justify-between lg:px-8">
                    <div>
                        <h2 class="text-2xl font-black">Siap mulai menggunakan Blueline OTP?</h2>
                        <p class="mt-2 text-sm leading-6 text-slate-300">Buat akun sekarang, top up saldo, pilih layanan, dan pantau kode OTP dari satu dashboard.</p>
                    </div>
                    <a href="{{ auth()->check() ? route('otp.index') : route('register') }}" class="w-fit rounded-md bg-emerald-500 px-5 py-3 text-sm font-black text-white hover:bg-emerald-400">Daftar Gratis</a>
                </div>
            </section>
        </main>

        <footer class="border-t border-slate-200 bg-white">
            <div class="mx-auto grid max-w-7xl gap-8 px-4 py-10 sm:px-6 md:grid-cols-[1fr_auto] lg:px-8">
                <div>
                    <img src="{{ asset('images/logo.png') }}" alt="{{ config('app.name', 'Blueline OTP') }} logo" class="h-10 w-auto max-w-[155px] object-contain">
                    <p class="mt-4 max-w-xl text-sm leading-7 text-slate-600">
                        Gunakan layanan hanya untuk kebutuhan verifikasi, testing, dan integrasi yang sah. Pengguna wajib mematuhi aturan platform dan hukum yang berlaku.
                    </p>
                </div>
                <div class="grid grid-cols-2 gap-8 text-sm">
                    <div>
                        <h3 class="font-black text-slate-950">Legal</h3>
                        <div class="mt-3 space-y-2 font-semibold text-slate-600">
                            <a href="{{ route('legal.terms') }}" class="block hover:text-emerald-700">Terms</a>
                            <a href="{{ route('legal.privacy') }}" class="block hover:text-emerald-700">Privacy</a>
                            <a href="{{ route('legal.refund') }}" class="block hover:text-emerald-700">Refund</a>
                        </div>
                    </div>
                    <div>
                        <h3 class="font-black text-slate-950">Bantuan</h3>
                        <div class="mt-3 space-y-2 font-semibold text-slate-600">
                            <a href="{{ route('legal.acceptable-use') }}" class="block hover:text-emerald-700">Acceptable Use</a>
                            <a href="{{ route('legal.contact') }}" class="block hover:text-emerald-700">Contact</a>
                            @auth
                                <a href="{{ route('support.index') }}" class="block hover:text-emerald-700">Support</a>
                            @else
                                <a href="{{ route('login') }}" class="block hover:text-emerald-700">Login</a>
                            @endauth
                        </div>
                    </div>
                </div>
            </div>
            <div class="border-t border-slate-200 px-4 py-5 text-center text-xs font-semibold text-slate-500">
                &copy; {{ now()->year }} {{ config('app.name', 'Blueline OTP') }}. All rights reserved.
            </div>
        </footer>
    </body>
</html>
