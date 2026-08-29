@props([
    'collapseId' => 'navbar-sticky',
])

<header
    id="app-navbar"
    {{ $attributes->merge(['class' => 'app-navbar safe-top safe-left safe-right fixed start-0 top-0 z-40 w-full']) }}
>
    <div class="app-navbar__inner mx-auto w-full max-w-container">
        <div class="flex items-center justify-between gap-4">
            {{-- Zona izquierda: logo + tabs --}}
            <div class="flex min-w-0 flex-1 items-center gap-6 lg:gap-8">
                <div class="shrink-0">
                    {{ $brand }}
                </div>

                @isset($links)
                    <div class="navbar-feed-tabs hidden md:flex">
                        {{ $links }}
                    </div>
                @endisset
            </div>

            {{-- Zona central: búsqueda (solo muro en desktop) --}}
                @isset($center)
                <div class="hidden min-w-0 flex-1 justify-center px-2 md:max-w-[320px] md:px-4 lg:flex lg:max-w-[480px] lg:min-w-[280px]">
                    {{ $center }}
                </div>
            @endisset

            {{-- Zona derecha: acciones --}}
            <div class="flex shrink-0 items-center gap-4">
                <div class="flex items-center gap-4">
                    {{ $actions }}
                </div>

                @if (isset($links) || isset($mobileExtras))
                    <button
                        data-collapse-toggle="{{ $collapseId }}"
                        type="button"
                        class="navbar-hamburger-btn md:hidden"
                        aria-controls="{{ $collapseId }}"
                        aria-expanded="false"
                        aria-label="Abrir menú de navegación"
                    >
                        <svg class="h-6 w-6" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <path stroke="currentColor" stroke-linecap="round" stroke-width="2" d="M5 7h14M5 12h14M5 17h14"/>
                        </svg>
                    </button>
                @endif
            </div>
        </div>

        {{-- Panel móvil colapsable --}}
        @if (isset($links) || isset($mobileExtras))
            <div id="{{ $collapseId }}" class="navbar-mobile-panel hidden md:hidden">
                @isset($mobileExtras)
                    <div class="mb-4 flex flex-col gap-4">
                        {{ $mobileExtras }}
                    </div>
                @endisset

                @isset($links)
                    <div class="navbar-feed-tabs flex flex-col items-start gap-1 sm:flex-row sm:items-center sm:gap-6">
                        {{ $links }}
                    </div>
                @endisset

                {{ $mobileLinks ?? '' }}
            </div>
        @endif
    </div>
</header>
