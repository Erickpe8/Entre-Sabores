<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Entre Sabores')</title>
    <meta name="description" content="@yield('meta_description', 'Entre Sabores — maridajes, cultura y comunidad gastronómica.')">
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-[100dvh] bg-base font-sans antialiased">
    <x-ui.alert-stack />
    <div class="relative flex min-h-[100dvh] flex-col overflow-hidden bg-base">
        @include('layouts.partials.marketing-header', ['active' => $active ?? null])

        <main class="relative z-10 es-container flex-1 py-10 sm:py-14 md:py-16">
            @yield('content')
        </main>

        <footer class="relative z-10 border-t border-line py-8 text-center text-caption text-muted">
            <p>&copy; {{ date('Y') }} Entre Sabores · <a href="{{ route('welcome') }}" class="text-link">Inicio</a></p>
        </footer>
    </div>
</body>
</html>
