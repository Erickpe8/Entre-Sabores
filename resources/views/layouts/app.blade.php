<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $title ?? config('app.name', 'Entre Sabores') }}</title>
        <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    @php($isProfileEdit = request()->routeIs('profile.edit') || request()->routeIs('user.profile'))
    <body class="font-sans antialiased {{ $isProfileEdit ? 'bg-slate-950' : '' }}">
        <div class="min-h-screen {{ $isProfileEdit ? '' : 'bg-gray-100' }}">
            @include('layouts.navigation')

            <!-- Page Heading -->
            @isset($header)
                <header class="{{ $isProfileEdit ? 'bg-white/5 border-b border-white/10 backdrop-blur' : 'bg-white shadow' }}">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <!-- Page Content (pt-16 compensa navbar fija) -->
            <main class="pt-16 {{ $isProfileEdit ? 'text-slate-100' : '' }}">
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
