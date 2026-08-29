<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $title ?? config('app.name', 'Entre Sabores') }}</title>
        @if (filled($metaDescription))
            <meta name="description" content="{{ $metaDescription }}">
        @endif
        @if (filled($metaDescription) || filled($ogImage) || filled($ogUrl))
            <meta property="og:title" content="{{ $title ?? config('app.name') }}">
            <meta property="og:type" content="article">
            @if (filled($metaDescription))
                <meta property="og:description" content="{{ $metaDescription }}">
            @endif
            @if (filled($ogImage))
                <meta property="og:image" content="{{ $ogImage }}">
            @endif
            <meta property="og:url" content="{{ $ogUrl ?? url()->current() }}">
            <meta name="twitter:card" content="summary_large_image">
        @endif
        <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased app-shell">
        <x-ui.alert-stack class="top-20" />
        <div class="flex min-h-[100dvh] flex-col bg-base">
            @include('layouts.navigation')

            @isset($header)
                <header class="border-b border-line bg-surface shadow-nav">
                    <div class="es-container py-6">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <main class="safe-nav-offset safe-bottom safe-left safe-right flex min-h-0 flex-1 flex-col">
                {{ $slot }}
            </main>
        </div>
    </body>
</html>
