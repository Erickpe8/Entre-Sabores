@props([
    'illustration',
    'title' => null,
    'message' => null,
])

<div {{ $attributes->merge(['class' => 'ui-empty-state']) }}>
    @if ($illustration)
        <x-ui.illustration :name="$illustration" class="ui-empty-state__art" />
    @endif

    @if ($title)
        <h3 class="ui-empty-state__title">{{ $title }}</h3>
    @endif

    @if ($message)
        <p class="ui-empty-state__message">{{ $message }}</p>
    @endif

    @if ($slot->isNotEmpty())
        <div class="ui-empty-state__actions">
            {{ $slot }}
        </div>
    @endif
</div>
