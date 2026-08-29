@props(['active'])

@php
$classes = ($active ?? false)
            ? 'block w-full border-l-4 border-fresh-500 bg-fresh-100 py-2 ps-3 pe-4 text-start text-base font-medium text-fresh-600 transition duration-150 ease-in-out focus:outline-none'
            : 'block w-full border-l-4 border-transparent py-2 ps-3 pe-4 text-start text-base font-medium text-ink-secondary transition duration-150 ease-in-out hover:border-warm-200 hover:bg-warm-100 hover:text-ink focus:outline-none';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
