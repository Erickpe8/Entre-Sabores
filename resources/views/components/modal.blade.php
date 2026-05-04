@props([
    'name',
    'show' => false,
    'maxWidth' => '2xl',
])

@php
$maxWidth = [
    'sm' => 'sm:max-w-sm',
    'md' => 'sm:max-w-md',
    'lg' => 'sm:max-w-lg',
    'xl' => 'sm:max-w-xl',
    '2xl' => 'sm:max-w-2xl',
][$maxWidth];

$initialOpen = $show ? '1' : '0';
$focusable = $attributes->has('focusable') ? '1' : '0';
@endphp

<div
    data-modal-root="{{ $name }}"
    data-modal-initial-open="{{ $initialOpen }}"
    data-modal-focusable="{{ $focusable }}"
    class="{{ $show ? '' : 'hidden' }} fixed inset-0 z-50 overflow-y-auto px-4 py-6 sm:px-0"
    aria-hidden="{{ $show ? 'false' : 'true' }}"
    role="dialog"
    aria-modal="true"
>
    <div class="flex min-h-full items-center justify-center">
        <div
            data-modal-backdrop
            class="fixed inset-0 transform bg-gray-500 transition-opacity duration-300 ease-out {{ $show ? 'opacity-75' : 'opacity-0' }}"
        ></div>

        <div
            data-modal-panel
            class="{{ $maxWidth }} mb-6 w-full transform overflow-hidden rounded-lg bg-white shadow-xl transition-all duration-300 ease-out sm:mx-auto {{ $show ? 'translate-y-0 opacity-100 sm:scale-100' : 'translate-y-4 opacity-0 sm:translate-y-0 sm:scale-95' }}"
        >
            {{ $slot }}
        </div>
    </div>
</div>
