@props([
    'title',
    'subtitle' => null,
])

<div {{ $attributes }}>
    <h1 class="auth-card-title">{{ $title }}</h1>
    @if (filled($subtitle))
        <p class="auth-card-subtitle">{{ $subtitle }}</p>
    @endif
</div>
