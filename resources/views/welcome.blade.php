@extends('layouts.marketing', ['active' => null, 'hideFooter' => true, 'compactMain' => true])

@section('title', 'Entre Sabores — Red social de maridajes y cultura gastronómica')

@section('meta_description', 'Comparte maridajes, descubre experiencias gastronómicas de distintas culturas y conecta con una comunidad global. Publica, explora y analiza combinaciones de comida y bebida.')

@section('canonical', route('welcome'))

@push('head')
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
@endpush

@section('content')
    <div class="welcome-landing space-y-12 lg:space-y-16">
        <section class="relative flex min-h-[calc(100dvh-5.5rem)] flex-col justify-center lg:min-h-[calc(100dvh-6.5rem)]">
            <div class="relative z-10 flex flex-col items-center gap-10 lg:flex-row lg:items-center lg:gap-12">
                <div class="min-w-0 max-w-2xl shrink-0 text-center lg:min-w-[min(100%,32rem)] lg:text-left">
                    <x-ui.badge class="mb-5">Comunidad gastronómica global</x-ui.badge>

                    <h1 class="mb-5 text-h1 leading-[1.12] md:text-6xl xl:text-7xl">
                        <span class="block">Explora sabores,</span>
                        <span class="block">conecta culturas</span>
                    </h1>

                    <p class="mx-auto mb-6 max-w-lg text-lg text-secondary md:text-xl lg:mx-0">
                        Comparte tus maridajes y descubre experiencias gastronómicas del mundo.
                    </p>

                    <h2 class="sr-only">Empieza en la comunidad</h2>

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

                <div class="relative flex w-full max-w-full items-center justify-center lg:w-auto lg:shrink-0 lg:justify-end">
                    <div class="pointer-events-none absolute inset-0 flex items-center justify-center" aria-hidden="true">
                        <div class="h-[min(100%,520px)] w-[min(100%,520px)] bg-hero-glow"></div>
                    </div>
                    <img
                        src="{{ asset('images/image-hero.png') }}"
                        alt="Ilustración de platos y bebidas de distintas culturas en Entre Sabores"
                        class="relative z-10 h-auto w-full max-w-[min(100%,22rem)] animate-float object-contain drop-shadow-[0_30px_80px_rgba(0,0,0,0.6)] sm:max-w-md md:max-w-lg lg:max-w-xl xl:max-w-2xl 2xl:max-w-[880px]"
                        width="880"
                        height="880"
                    />
                </div>
            </div>
        </section>

        <section class="relative z-10 border-t border-line pt-10 pb-4 lg:pt-12" aria-labelledby="welcome-why-heading">
            <h2 id="welcome-why-heading" class="mb-4 text-center text-2xl font-bold text-heading lg:text-left">
                Una comunidad para compartir maridajes
            </h2>
            <p class="mx-auto max-w-2xl text-center text-secondary lg:mx-0 lg:text-left">
                Publica combinaciones de comida y bebida con contexto cultural, explora el muro con distintos modos de orden
                y recibe análisis asistido cuando el servidor lo tiene configurado.
            </p>
        </section>
    </div>
@endsection
