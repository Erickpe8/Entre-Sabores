<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Entre Sabores</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Permanent+Marker&display=swap" rel="stylesheet">
    <style>
        .hero-title {
            font-family: 'Permanent Marker', cursive;
            letter-spacing: 0.5px;
            line-height: 1.05;
        }

        @keyframes float {
            0%, 100% {
                transform: translate3d(0, 0, 0) rotate(0deg) scale(1);
            }
            25% {
                transform: translate3d(3px, -7px, 0) rotate(0.35deg) scale(1.02);
            }
            50% {
                transform: translate3d(0, -12px, 0) rotate(-0.4deg) scale(1.03);
            }
            75% {
                transform: translate3d(-3px, -6px, 0) rotate(0.3deg) scale(1.015);
            }
        }

        .animate-float {
            animation: float 7.5s ease-in-out infinite;
            transform-origin: 50% 60%;
            will-change: transform;
        }
    </style>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-[90vh] bg-slate-950 font-['Inter'] text-white antialiased">
    <section class="relative flex min-h-screen h-full flex-col overflow-hidden bg-gradient-to-br from-slate-950 via-slate-900 to-emerald-950/95">

        <header class="relative z-20">
            <div class="mx-auto flex w-full max-w-[1400px] items-center justify-between px-8 py-6 md:py-7">
                <a href="/" class="flex items-center gap-3">
                    <img
                        src="{{ asset('favicon.png') }}"
                        alt="Logo Entre Sabores"
                        class="h-10 w-10 object-contain"
                    >
                    <span class="text-lg font-extrabold tracking-tight">Entre Sabores</span>
                </a>

                <nav class="hidden items-center gap-8 text-base font-semibold text-white/90 lg:flex">
                    <a href="#" class="transition hover:text-cyan-300">Inicio</a>
                    <a href="#explorar" class="transition hover:text-cyan-300">Explorar</a>
                    <a href="#como-funciona" class="transition hover:text-cyan-300">Cómo funciona</a>
                </nav>

                <div class="flex items-center gap-3">
                    <a href="{{ route('login') }}" class="rounded-xl border border-white/20 bg-white/5 px-6 py-3 text-base font-semibold transition hover:bg-white/10">Iniciar sesión</a>
                    <a href="{{ route('register') }}" class="rounded-xl bg-gradient-to-r from-cyan-300 to-emerald-300 px-6 py-3 text-base font-bold text-slate-900 shadow-md shadow-cyan-500/20 transition hover:brightness-110">Registrarse</a>
                </div>
            </div>
        </header>

        <main class="relative z-10 mx-auto flex w-full max-w-[1400px] flex-1 flex-col items-center justify-between gap-10 px-8 pt-10 pb-12 lg:flex-row lg:gap-10 xl:gap-16 lg:py-16">
            <div class="-mt-6 max-w-2xl space-y-5 text-center lg:-mt-10 lg:text-left">
                <p class="inline-flex rounded-full border border-white/20 bg-white/5 px-3 py-1 text-[10px] font-semibold uppercase tracking-[0.18em] text-cyan-200/90">
                    COMUNIDAD GASTRONÓMICA GLOBAL
                </p>

                <h1 class="hero-title text-5xl font-extrabold leading-tight tracking-wide md:text-6xl xl:text-7xl">
                    Explora sabores, conecta culturas
                </h1>

                <p class="mx-auto max-w-lg text-lg leading-relaxed text-slate-300 md:text-xl lg:mx-0">
                    Comparte tus maridajes y descubre experiencias gastronómicas del mundo.
                </p>

                <div class="mt-6 flex flex-col items-center gap-6 sm:flex-row lg:items-start">
                    <a href="{{ route('register') }}" class="inline-flex w-full items-center justify-center rounded-xl bg-gradient-to-r from-emerald-300 via-cyan-300 to-indigo-300 px-8 py-4 text-lg font-bold text-slate-900 shadow-lg shadow-emerald-500/20 transition hover:brightness-110 sm:w-auto">
                        Comenzar ahora
                    </a>
                    <a href="{{ route('login') }}" class="inline-flex w-full items-center justify-center rounded-xl border border-white/30 bg-transparent px-8 py-4 text-lg font-medium text-white transition hover:bg-white/10 sm:w-auto">
                        Ya tengo cuenta
                    </a>
                </div>
            </div>

            <div class="flex items-center justify-center lg:justify-end">
                <img
                    src="{{ asset('images/image-hero.png') }}"
                    alt="Hero Entre Sabores"
                    class="w-[660px] object-contain drop-shadow-[0_30px_80px_rgba(0,0,0,0.6)] md:w-[730px] xl:w-[880px] 2xl:w-[980px] animate-float"
                />
            </div>
        </main>

    </section>
</body>
</html>
