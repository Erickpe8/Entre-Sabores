@extends('layouts.marketing', ['active' => 'explore'])

@section('title', 'Explorar — Entre Sabores')

@section('meta_description', 'Descubre maridajes y experiencias en el muro de Entre Sabores: exploración global, cuentas que sigues y filtros de orden.')

@section('content')
    <article class="mx-auto max-w-3xl space-y-10">
        <header class="space-y-4 text-center sm:text-left">
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-cyan-200/80">Explorar</p>
            <h1 class="text-3xl font-extrabold tracking-tight text-ink sm:text-4xl md:text-5xl">
                Tu ventana a maridajes y culturas
            </h1>
            <p class="text-lg leading-relaxed text-ink-secondary">
                En el muro puedes recorrer publicaciones de la comunidad: combinaciones de comida y bebida contadas en primera persona, con etiquetas que sitúan cada experiencia.
            </p>
        </header>

        <div class="space-y-6 rounded-2xl border border-warm-200 bg-white/[0.04] p-6 sm:p-8 backdrop-blur-sm">
            <h2 class="text-lg font-bold text-ink">Qué puedes hacer</h2>
            <ul class="space-y-4 text-ink-secondary">
                <li class="flex gap-3">
                    <span class="mt-1 flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-emerald-500/20 text-xs font-bold text-fresh-600">1</span>
                    <span><strong class="text-ink">Explorar (FYP)</strong> — descubre publicaciones de toda la comunidad y encuentra nuevas voces.</span>
                </li>
                <li class="flex gap-3">
                    <span class="mt-1 flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-emerald-500/20 text-xs font-bold text-fresh-600">2</span>
                    <span><strong class="text-ink">Siguiendo</strong> — cuando tengas cuenta, enfoca el feed en personas que sigues (requiere iniciar sesión).</span>
                </li>
                <li class="flex gap-3">
                    <span class="mt-1 flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-emerald-500/20 text-xs font-bold text-fresh-600">3</span>
                    <span><strong class="text-ink">Orden y búsqueda</strong> — combina «Recientes», «Populares» o «Tendencia» con texto y etiquetas para acotar lo que ves.</span>
                </li>
            </ul>
        </div>

        <div class="flex flex-col items-center justify-center gap-4 sm:flex-row sm:gap-6">
            @auth
                <a
                    href="{{ route('dashboard') }}"
                    class="btn inline-flex w-full max-w-xs bg-gradient-to-r from-emerald-300 via-cyan-300 to-indigo-300 px-8 py-3 text-base font-bold text-slate-900 shadow-lg shadow-emerald-500/20 hover:brightness-110 sm:w-auto"
                >Abrir el muro</a>
            @else
                <a
                    href="{{ route('register') }}"
                    class="btn inline-flex w-full max-w-xs bg-gradient-to-r from-emerald-300 via-cyan-300 to-indigo-300 px-8 py-3 text-base font-bold text-slate-900 shadow-lg shadow-emerald-500/20 hover:brightness-110 sm:w-auto"
                >Crear cuenta</a>
                <a
                    href="{{ route('login') }}"
                    class="btn inline-flex w-full max-w-xs border border-white/30 bg-transparent px-8 py-3 text-base font-medium text-ink hover:bg-warm-100 sm:w-auto"
                >Ya tengo cuenta</a>
            @endauth
        </div>

        <p class="text-center text-sm text-ink-muted">
            ¿Primera vez? Lee <a href="{{ route('how-it-works') }}" class="font-medium text-fresh-500 underline-offset-2 hover:underline">Cómo funciona</a>.
        </p>
    </article>
@endsection
