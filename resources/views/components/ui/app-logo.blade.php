@props([
    'class' => 'h-7 w-7',
    'logoSrc' => null,
    'alt' => '',
])

<img
    {{ $attributes->merge([
        'class' => $class . ' shrink-0 object-contain',
        'src' => $logoSrc ?? asset('images/app-logo.svg'),
        'alt' => $alt,
        'width' => '48',
        'height' => '48',
    ]) }}
    @if ($alt === '') aria-hidden="true" @endif
>
