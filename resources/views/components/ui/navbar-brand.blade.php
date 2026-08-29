@props([
    'homeUrl' => '/',
    'logoSrc' => null,
    'logoAlt' => 'Entre Sabores',
])

<a {{ $attributes->merge(['href' => $homeUrl, 'class' => 'flex items-center space-x-3 rtl:space-x-reverse']) }}>
    <x-ui.app-logo class="h-7 w-7 shrink-0" :logo-src="$logoSrc" :alt="$logoAlt" />
    <span class="self-center whitespace-nowrap text-xl font-semibold text-heading">
        {{ $slot }}
    </span>
</a>
