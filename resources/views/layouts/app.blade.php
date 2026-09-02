<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        @php
            $seoTitle = $title ?? config('app.name');
            $seoDescription = $metaDescription ?? config('seo.default_description');
        @endphp
        <x-seo-head
            :title="$seoTitle"
            :description="$seoDescription"
            :canonical="url()->current()"
            :og-image="filled($ogImage ?? null) ? $ogImage : null"
            og-type="website"
            :include-website-json-ld="false"
        />
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
