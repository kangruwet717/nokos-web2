<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ $title ?? config('app.name', 'Nokos') }}</title>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-stone-50 font-sans text-slate-950">
        <header class="border-b border-slate-200 bg-white">
            <div class="mx-auto flex max-w-5xl items-center justify-between px-4 py-4 sm:px-6 lg:px-8">
                <a href="{{ url('/') }}" class="flex items-center gap-3">
                    <span class="grid h-10 w-10 place-items-center rounded-lg bg-emerald-600 text-sm font-bold text-white">NX</span>
                    <span class="text-lg font-bold">Nokos</span>
                </a>
                <nav class="flex items-center gap-2 text-sm font-semibold">
                    <a href="{{ route('legal.terms') }}" class="rounded-lg px-3 py-2 text-slate-700 hover:bg-slate-100">Terms</a>
                    <a href="{{ route('legal.privacy') }}" class="rounded-lg px-3 py-2 text-slate-700 hover:bg-slate-100">Privacy</a>
                    <a href="{{ route('legal.contact') }}" class="rounded-lg px-3 py-2 text-slate-700 hover:bg-slate-100">Contact</a>
                </nav>
            </div>
        </header>

        <main class="mx-auto max-w-5xl px-4 py-10 sm:px-6 lg:px-8">
            <article class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
                <p class="text-sm font-bold uppercase text-emerald-700">Legal</p>
                <h1 class="mt-2 text-3xl font-extrabold text-slate-950">{{ $title }}</h1>
                <p class="mt-2 text-sm text-slate-500">Versi: {{ config('app.legal_terms_version', '2026-05-27') }}</p>

                <div class="prose prose-slate mt-8 max-w-none prose-headings:text-slate-950 prose-a:text-emerald-700">
                    {{ $slot }}
                </div>
            </article>
        </main>
    </body>
</html>
