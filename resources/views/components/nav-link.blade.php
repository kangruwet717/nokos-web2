@props(['active'])

@php
$classes = ($active ?? false)
            ? 'inline-flex items-center px-2 pt-1 border-b-2 border-emerald-500 text-sm font-semibold leading-5 text-slate-950 focus:outline-none focus:border-emerald-700 transition duration-150 ease-in-out'
            : 'inline-flex items-center px-2 pt-1 border-b-2 border-transparent text-sm font-semibold leading-5 text-slate-500 hover:text-slate-800 hover:border-slate-300 focus:outline-none focus:text-slate-800 focus:border-slate-300 transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
