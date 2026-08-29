@props(['value' => null])

<span {{ $attributes->merge(['class' => 'badge']) }}>
    {{ $value ?? $slot }}
</span>
