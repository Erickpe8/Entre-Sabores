@props([
    'active' => false,
    'href' => '#',
    'as' => 'a',
])

@php
    $activeClasses = 'block rounded-base bg-surface px-3 py-2 text-gold underline underline-offset-4 md:bg-transparent md:p-0';
    $idleClasses = 'nav-link block rounded-base px-3 py-2 hover:bg-surface-hover md:p-0 md:hover:bg-transparent';
    $classes = ($active ? $activeClasses : $idleClasses) . ' md:inline-block';
@endphp

@if ($as === 'button')
    <button type="button" {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </button>
@else
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }} @if($active) aria-current="page" @endif>
        {{ $slot }}
    </a>
@endif
