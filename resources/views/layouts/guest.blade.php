<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $title ?? config('app.name', 'Entre Sabores') }}</title>
        <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-['Inter'] text-slate-100 antialiased">
        <main class="h-screen flex items-center justify-center overflow-hidden px-6 bg-gradient-to-br from-slate-950 via-slate-900 to-emerald-950">
            <div class="grid lg:grid-cols-2 gap-10 w-full max-w-6xl items-center h-full">
                <section class="flex items-center justify-center h-full">
                    <div class="w-full max-w-lg">
                        <a href="/" class="mb-8 inline-flex items-center gap-3">
                            <img
                                src="{{ asset('favicon.png') }}"
                                alt="Logo Entre Sabores"
                                class="h-10 w-10 object-contain"
                            >
                            <h1 class="text-xl font-bold text-white tracking-wide">
                                Entre <span class="text-green-400">Sabores</span>
                            </h1>
                        </a>
                        {{ $slot }}
                    </div>
                </section>

                <aside class="hidden lg:flex items-center justify-center relative">
                    <div class="relative z-10 flex items-center justify-center w-full">
                        <img
                            src="{{ asset('images/hero-gallery.png') }}"
                            alt="Entre Sabores"
                            class="w-[520px] xl:w-[650px] 2xl:w-[750px] object-contain scale-110 drop-shadow-2xl"
                        >
                    </div>
                </aside>
            </div>
        </main>
    </body>
</html>
