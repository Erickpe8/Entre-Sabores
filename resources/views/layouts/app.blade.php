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
        <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    @php($isProfileEdit = request()->routeIs('settings.profile') || request()->routeIs('settings.account') || request()->routeIs('profile.show'))
    <body class="font-sans antialiased {{ $isProfileEdit ? 'bg-slate-950 text-slate-100' : 'bg-gray-100 text-slate-900' }}">
        <div class="min-h-[100dvh] flex flex-col {{ $isProfileEdit ? '' : 'bg-gray-100' }}">
            @include('layouts.navigation')

            <!-- Page Heading -->
            @isset($header)
                <header class="{{ $isProfileEdit ? 'bg-white/5 border-b border-white/10 backdrop-blur' : 'bg-white shadow' }}">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <!-- Page Content -->
            <main class="safe-nav-offset safe-bottom safe-left safe-right flex-1 {{ $isProfileEdit ? 'text-slate-100' : '' }}">
                {{ $slot }}
            </main>
        </div>

        <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>
        <script>
            if (typeof lucide !== 'undefined' && typeof lucide.createIcons === 'function') {
                lucide.createIcons();
            }
        </script>
    </body>
</html>
