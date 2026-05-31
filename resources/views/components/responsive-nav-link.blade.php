@props(['active'])

@php
$classes = ($active ?? false)
            ? 'block w-full ps-3 pe-4 py-2 border-l-4 border-emerald-500 text-start text-base font-semibold text-emerald-800 bg-emerald-50 focus:outline-none focus:text-emerald-900 focus:bg-emerald-100 focus:border-emerald-700 transition duration-150 ease-in-out dark:bg-emerald-950 dark:text-emerald-200 dark:focus:bg-emerald-900'
            : 'block w-full ps-3 pe-4 py-2 border-l-4 border-transparent text-start text-base font-semibold text-slate-600 hover:text-slate-800 hover:bg-slate-50 hover:border-slate-300 focus:outline-none focus:text-slate-800 focus:bg-slate-50 focus:border-slate-300 transition duration-150 ease-in-out dark:text-slate-300 dark:hover:bg-slate-800 dark:hover:text-white dark:hover:border-slate-600 dark:focus:bg-slate-800 dark:focus:text-white dark:focus:border-slate-600';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
