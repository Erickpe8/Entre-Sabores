<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Entre Sabores</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <style>
        @keyframes float {
            0%, 100% { transform: translate3d(0, 0, 0) rotate(0deg) scale(1); }
            25% { transform: translate3d(3px, -7px, 0) rotate(0.35deg) scale(1.02); }
            50% { transform: translate3d(0, -12px, 0) rotate(-0.4deg) scale(1.03); }
            75% { transform: translate3d(-3px, -6px, 0) rotate(0.3deg) scale(1.015); }
        }
        .animate-float {
            animation: float 7.5s ease-in-out infinite;
            transform-origin: 50% 60%;
            will-change: transform;
        }
    </style>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-[100dvh] bg-base font-sans antialiased">
    <x-ui.alert-stack />
    <section class="relative flex min-h-[100dvh] flex-col overflow-hidden bg-base">
        @include('layouts.partials.marketing-header', ['active' => null])

        <main class="relative z-10 es-container flex flex-1 flex-col items-center justify-between gap-10 pt-10 pb-12 lg:flex-row lg:gap-12 lg:py-16">
            <div class="min-w-[min(100%,18rem)] max-w-2xl shrink-0 text-center lg:-mt-10 lg:min-w-[min(100%,32rem)] lg:text-left">
                <x-ui.badge class="mb-5">Comunidad gastronómica global</x-ui.badge>

                <h1 class="mb-5 text-h1 leading-[1.08] md:text-6xl xl:text-7xl">
                    <span class="block">Explora sabores,</span>
                    <span class="block">conecta culturas</span>
                </h1>

                <p class="mx-auto mb-6 max-w-lg text-lg text-secondary md:text-xl lg:mx-0">
                    Comparte tus maridajes y descubre experiencias gastronómicas del mundo.
                </p>

                <div class="flex flex-col items-center gap-4 sm:flex-row lg:items-start">
                    <a href="{{ route('register') }}" class="btn btn-primary w-full text-lg sm:w-auto">
                        Comenzar ahora
                    </a>
                    <a href="{{ route('login') }}" class="btn btn-secondary w-full text-lg sm:w-auto">
                        Ya tengo cuenta
                    </a>
                </div>

                <x-ui.community-social-proof class="mt-6" />
            </div>

            <div class="relative flex items-center justify-center lg:justify-end">
                <div class="pointer-events-none absolute inset-0 flex items-center justify-center" aria-hidden="true">
                    <div class="h-[min(100%,520px)] w-[min(100%,520px)] bg-hero-glow"></div>
                </div>
                <img
                    src="{{ asset('images/image-hero.png') }}"
                    alt="Hero Entre Sabores"
                    class="relative z-10 w-[660px] animate-float object-contain drop-shadow-[0_30px_80px_rgba(0,0,0,0.6)] md:w-[730px] xl:w-[880px] 2xl:w-[980px]"
                />
            </div>
        </main>
    </section>
</body>
</html>
