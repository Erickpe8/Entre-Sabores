{{--
    Navegación marketing: Explorar | Cómo funciona
    $active: null | 'explore' | 'how-it-works'
--}}
@php
    $active = $active ?? null;
    $navLink = 'group relative inline-flex items-center gap-2 rounded-base px-3 py-2.5 text-[15px] font-medium tracking-tight outline-none transition-all duration-200 ease-out focus-visible:ring-2 focus-visible:ring-warm/40 focus-visible:ring-offset-2 focus-visible:ring-offset-base';
    $navIdle = 'nav-link text-secondary hover:text-warm';
    $navActive = 'nav-link active text-gold';
@endphp

<header class="relative z-20 border-b border-line bg-base/90 backdrop-blur-md">
    <div class="es-container flex flex-wrap items-center gap-x-3 gap-y-3 py-3.5 sm:flex-nowrap sm:gap-6 sm:py-4">
        <a
            href="{{ route('welcome') }}"
            aria-label="Entre Sabores, inicio"
            class="flex shrink-0 items-center gap-2.5 whitespace-nowrap rounded-base outline-none transition-opacity hover:opacity-95 focus-visible:ring-2 focus-visible:ring-warm/40"
        >
            <x-ui.app-logo class="h-9 w-9 shrink-0 sm:h-10 sm:w-10" />
            <span class="hidden text-lg font-bold tracking-tight text-primary sm:inline">Entre <span class="text-warm">Sabores</span></span>
        </a>

        <nav
            class="flex flex-1 flex-wrap items-center justify-center gap-1 sm:justify-start sm:gap-1 md:gap-2"
            aria-label="Secciones principales"
        >
            <a
                href="{{ route('explore') }}"
                @if ($active === 'explore') aria-current="page" @endif
                class="{{ $navLink }} {{ $active === 'explore' ? $navActive : $navIdle }}"
            >
                <svg class="h-4 w-4 shrink-0 text-secondary transition-colors duration-200 group-hover:text-warm" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" />
                </svg>
                <span class="whitespace-nowrap">Explorar</span>
            </a>

            <a
                href="{{ route('how-it-works') }}"
                @if ($active === 'how-it-works') aria-current="page" @endif
                class="{{ $navLink }} {{ $active === 'how-it-works' ? $navActive : $navIdle }}"
            >
                <svg class="h-4 w-4 shrink-0 text-secondary transition-colors duration-200 group-hover:text-warm" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
                </svg>
                <span class="whitespace-nowrap">Cómo funciona</span>
            </a>
        </nav>

        <div class="flex shrink-0 items-center justify-end gap-2 sm:ml-auto sm:gap-3">
            @auth
                <a href="{{ route('dashboard') }}" class="btn btn-secondary text-sm">Ir al muro</a>
            @else
                <a href="{{ route('login') }}" class="btn btn-secondary text-sm">Iniciar sesión</a>
                <a href="{{ route('register') }}" class="btn btn-primary text-sm">Registrarse</a>
            @endauth
        </div>
    </div>
</header>
