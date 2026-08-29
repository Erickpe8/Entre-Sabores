@props(['align' => 'right', 'width' => '48', 'contentClasses' => 'py-1 app-dropdown-panel'])

@php
$alignmentClasses = match ($align) {
    'left' => 'ltr:origin-top-left rtl:origin-top-right start-0',
    'top' => 'origin-top',
    default => 'ltr:origin-top-right rtl:origin-top-left end-0',
};

$width = match ($width) {
    '48' => 'w-48',
    '52' => 'w-52',
    default => $width,
};
@endphp

<div class="relative" data-dropdown>
    <div data-dropdown-trigger>
        {{ $trigger }}
    </div>

    <div
        data-dropdown-panel
        class="absolute z-50 mt-2 hidden origin-top scale-95 opacity-0 transition duration-150 ease-out {{ $width }} {{ $alignmentClasses }}"
        role="menu"
    >
        <div class="{{ $contentClasses }}">
            {{ $content }}
        </div>
    </div>
</div>
