@props([
    'type' => 'danger',
    'message' => '',
    'title' => null,
    'dismissible' => true,
])

@php
    $variants = [
        'info' => 'alert-info',
        'danger' => 'alert-danger',
        'success' => 'alert-success',
        'warning' => 'alert-warning',
        'dark' => 'alert-info',
    ];
    $variantClass = $variants[$type] ?? $variants['danger'];
@endphp

<div
    {{ $attributes->merge([
        'class' => "pointer-events-auto flex items-start gap-3 rounded-base border p-4 text-sm font-medium shadow-lg transition-all duration-300 {$variantClass}",
        'role' => 'alert',
        'data-alert-auto-dismiss' => '4500',
        'data-alert-static' => '1',
    ]) }}
>
    <div class="min-w-0 flex-1">
        @if ($title)
            <span class="font-medium">{{ $title }}</span>
            <span class="ml-1">{{ $message }}</span>
        @else
            {{ $message }}
        @endif
    </div>

    @if ($dismissible)
        <button
            type="button"
            class="alert-dismiss -m-1 shrink-0 rounded-base p-1 opacity-70 transition hover:opacity-100 focus:outline-none focus:ring-2 focus:ring-current/30"
            aria-label="Cerrar alerta"
        >
            <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
            </svg>
        </button>
    @endif
</div>
