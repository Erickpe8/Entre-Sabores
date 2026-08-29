@props(['active', 'dark' => false])

@php
$classes = ($active ?? false)
            ? (($dark ?? false)
                ? 'inline-flex items-center px-1 pt-1 border-b-2 border-fresh-500 text-sm font-medium leading-5 text-ink focus:outline-none transition duration-150 ease-in-out'
                : 'inline-flex items-center px-1 pt-1 border-b-2 border-fresh-500 text-sm font-medium leading-5 text-gray-900 focus:outline-none focus:border-indigo-700 transition duration-150 ease-in-out')
            : (($dark ?? false)
                ? 'inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-ink-secondary hover:text-ink hover:border-white/30 focus:outline-none transition duration-150 ease-in-out'
                : 'inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-gray-500 hover:text-ink hover:border-warm-200 focus:outline-none focus:text-ink focus:border-warm-200 transition duration-150 ease-in-out');
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
