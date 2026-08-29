@php
    $isWall = request()->routeIs('dashboard');
    $feedFollowing = $isWall && request()->boolean('following');
    $user = auth()->user();
    $initials = '';
    if ($user) {
        $initials = strtoupper(
            mb_substr((string) ($user->first_name ?? ''), 0, 1)
            .mb_substr((string) ($user->last_name ?? ''), 0, 1)
        );
        if (trim($initials) === '') {
            $initials = strtoupper(mb_substr((string) ($user->username ?? 'U'), 0, 2));
        }
    }
@endphp

<x-ui.navbar collapse-id="navbar-sticky">
    <x-slot name="brand">
        <x-ui.navbar-brand :home-url="route('dashboard')">
            Entre Sabores
        </x-ui.navbar-brand>
    </x-slot>

    @if ($isWall)
        <x-slot name="center">
            @include('layouts.partials.wall-search', ['inputId' => 'wall-search-q'])
        </x-slot>

        <x-slot name="mobileExtras">
            @include('layouts.partials.wall-search', ['inputId' => 'wall-search-q-mobile'])
            <nav class="navbar-feed-tabs wall-feed-tabs flex-col items-start sm:flex-row sm:items-center" aria-label="Feed principal">
                @include('layouts.partials.wall-feed-tabs')
            </nav>
            @include('layouts.partials.wall-filter-pills')
        </x-slot>
    @endif

    <x-slot name="links">
        @unless ($isWall)
            @include('layouts.partials.wall-feed-tabs')
        @endunless
    </x-slot>

    <x-slot name="mobileLinks">
        @auth
            <ul class="mt-4 flex flex-col gap-1 border-t border-default pt-4">
                <li>
                    <a href="{{ route('profile.show', auth()->user()->username) }}" class="app-dropdown-link rounded-base">
                        Mi perfil
                    </a>
                </li>
                <li>
                    <a href="{{ route('settings.profile') }}" class="app-dropdown-link rounded-base">
                        Configuración
                    </a>
                </li>
                <li>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="app-dropdown-link rounded-base text-accent-warm">
                            Cerrar sesión
                        </button>
                    </form>
                </li>
            </ul>
        @else
            <ul class="mt-4 flex flex-col gap-1 border-t border-default pt-4">
                <li>
                    <a href="{{ route('login') }}" class="app-dropdown-link rounded-base">
                        Iniciar sesión
                    </a>
                </li>
            </ul>
        @endauth
    </x-slot>

    <x-slot name="actions">
        @auth
            @include('layouts.partials.nav-notifications')

            <x-dropdown align="right" width="52" contentClasses="py-1 app-dropdown-panel">
                <x-slot name="trigger">
                    <button
                        type="button"
                        class="navbar-user-trigger"
                        aria-label="Menú de cuenta"
                        aria-expanded="false"
                        aria-haspopup="menu"
                    >
                        @if ($user?->profile_photo_url)
                            <img
                                src="{{ $user->profile_photo_url }}"
                                alt=""
                                class="navbar-user-trigger__avatar"
                                width="36"
                                height="36"
                                loading="lazy"
                                decoding="async"
                                onerror="this.hidden=true; this.nextElementSibling?.classList.remove('hidden')"
                            />
                            <span class="navbar-user-trigger__initials hidden" aria-hidden="true">{{ $initials }}</span>
                        @else
                            <span class="navbar-user-trigger__initials" aria-hidden="true">{{ $initials }}</span>
                        @endif
                        <svg class="navbar-user-trigger__chevron hidden sm:block" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </button>
                </x-slot>

                <x-slot name="content">
                    <x-dropdown-link :href="route('dashboard')">Ir al muro</x-dropdown-link>
                    <x-dropdown-link :href="route('profile.show', Auth::user()->username)">Mi perfil</x-dropdown-link>
                    <x-dropdown-link :href="route('settings.profile')">Configuración</x-dropdown-link>

                    <div class="my-1 border-t border-default"></div>

                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <button
                            type="submit"
                            class="app-dropdown-link text-accent-warm"
                        >
                            Cerrar sesión
                        </button>
                    </form>
                </x-slot>
            </x-dropdown>
        @else
            <a href="{{ route('register') }}" class="btn btn-primary text-sm">
                Crear cuenta
            </a>
        @endauth
    </x-slot>
</x-ui.navbar>
