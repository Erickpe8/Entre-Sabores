@props([
    'name',
    'lazy' => true,
    'imgClass' => '',
])

@php
    $resolved = \App\Support\Illustrations::resolve($name);
@endphp

@if ($resolved)
    <picture {{ $attributes->class('ui-illustration') }}>
        @if ($resolved['webp'])
            <source srcset="{{ $resolved['webp'] }}" type="image/webp">
        @endif
        <img
            src="{{ $resolved['png'] }}"
            alt="{{ $resolved['alt'] }}"
            @if ($imgClass) class="{{ $imgClass }}" @endif
            @if ($resolved['width']) width="{{ $resolved['width'] }}" @endif
            @if ($resolved['height']) height="{{ $resolved['height'] }}" @endif
            @if ($lazy) loading="lazy" @else loading="eager" @endif
            decoding="async"
            draggable="false"
        >
    </picture>
@endif
