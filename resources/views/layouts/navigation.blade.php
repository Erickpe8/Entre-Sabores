@php($isWall = request()->routeIs('dashboard'))
@php($isProfileArea = request()->routeIs('profile.edit') || request()->routeIs('user.profile'))
@php($navDark = $isWall || $isProfileArea)
@php($feedFollowing = $isWall && request()->boolean('following'))
@php($centerInactive = $navDark
    ? 'inline-flex items-center justify-center rounded-full px-4 py-2 text-sm font-medium text-slate-400 transition hover:bg-white/8 hover:text-slate-100'
    : 'inline-flex items-center justify-center rounded-full px-4 py-2 text-sm font-medium text-stone-500 transition hover:bg-stone-50 hover:text-stone-800')

<nav x-data="{ open: false }" class="{{ $navDark ? 'bg-[#0b1120]/95 border-b border-white/10 backdrop-blur-md' : 'bg-white border-b border-stone-100' }} safe-top safe-left safe-right fixed top-0 inset-x-0 z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-14 sm:h-16 flex items-center gap-2">
        {{-- Izquierda: marca --}}
        <div class="flex items-center gap-2 min-w-0 flex-1 justify-start">
            <a href="{{ route('dashboard') }}" class="flex items-center gap-2 shrink-0 min-w-0">
                <img
                    src="{{ asset('favicon.png') }}"
                    alt="Logo Entre Sabores"
                    class="h-6 sm:h-8 w-auto object-contain"
                >
                <span class="{{ $navDark ? 'text-white' : 'text-stone-800' }} hidden sm:inline font-semibold text-sm tracking-tight truncate">Entre Sabores</span>
            </a>
        </div>

        {{-- Centro: FYP | Siguiendo --}}
        @if (! $isProfileArea)
        <div class="flex items-center justify-center shrink-0">
            @if ($isWall)
                <div class="toggle-container">
                    <div
                        id="navbar-feed-slider"
                        class="toggle-slider"
                        style="transform: {{ $feedFollowing ? 'translateX(100%)' : 'translateX(0%)' }};"
                    ></div>

                    <button
                        type="button"
                        id="btn-fyp"
                        data-navbar-feed="fyp"
                        class="toggle-btn {{ ! $feedFollowing ? 'active' : '' }}"
                        aria-pressed="{{ ! $feedFollowing ? 'true' : 'false' }}"
                    >
                        FYP
                    </button>
                    <button
                        type="button"
                        id="btn-following"
                        data-navbar-feed="following"
                        class="toggle-btn {{ $feedFollowing ? 'active' : '' }}"
                        title="{{ auth()->check() ? 'Solo cuentas que sigues' : 'Inicia sesión' }}"
                        aria-pressed="{{ $feedFollowing ? 'true' : 'false' }}"
                    >
                        Siguiendo
                    </button>
                </div>
            @else
                <a href="{{ route('dashboard') }}" class="{{ $centerInactive }}">
                    FYP
                </a>
                <a href="{{ route('dashboard', ['following' => 1]) }}" class="{{ $centerInactive }}" title="{{ auth()->check() ? '' : 'Inicia sesión' }}">
                    Siguiendo
                </a>
            @endif
        </div>
        @endif

        {{-- Derecha: avatar / auth --}}
        <div class="flex items-center justify-end gap-2 flex-1 min-w-0">
            <div class="hidden sm:flex sm:items-center shrink-0">
                @auth
                    <x-dropdown
                        align="right"
                        width="48"
                        :contentClasses="$navDark ? 'py-1 bg-slate-900 border border-slate-700' : 'py-1 bg-white'"
                    >
                        <x-slot name="trigger">
                            <button type="button" class="inline-flex items-center gap-2 rounded-full px-1.5 py-1 border transition {{ $navDark ? 'bg-white/5 border-white/15 hover:bg-white/10 focus:ring-white/40 focus:ring-offset-slate-900' : 'bg-white border-stone-200 hover:bg-stone-50 focus:ring-amber-500 focus:ring-offset-white' }} focus:outline-none focus:ring-2 focus:ring-offset-2" aria-label="Menú de cuenta">
                                <img
                                    src="{{ Auth::user()->profile_photo_url }}"
                                    alt=""
                                    class="h-9 w-9 rounded-full object-cover border-2 {{ $navDark ? 'border-white/20' : 'border-stone-200' }}"
                                />
                                <svg class="hidden md:block fill-current h-4 w-4 opacity-60 {{ $navDark ? 'text-slate-200' : 'text-stone-500' }}" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        </x-slot>

                        <x-slot name="content">
                            <x-dropdown-link
                                :href="route('dashboard')"
                                class="{{ $navDark ? 'text-slate-200 hover:bg-slate-800 focus:bg-slate-800' : '' }}"
                            >Ir al muro</x-dropdown-link>

                            <x-dropdown-link
                                :href="route('profile.edit')"
                                class="{{ $navDark ? 'text-slate-200 hover:bg-slate-800 focus:bg-slate-800' : '' }}"
                            >Perfil</x-dropdown-link>

                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <x-dropdown-link :href="route('logout')"
                                        class="{{ $navDark ? 'text-slate-200 hover:bg-slate-800 focus:bg-slate-800' : '' }}"
                                        onclick="event.preventDefault(); this.closest('form').submit();">
                                    Cerrar sesión
                                </x-dropdown-link>
                            </form>
                        </x-slot>
                    </x-dropdown>
                @else
                    <div class="flex items-center gap-3">
                        <a href="{{ route('login') }}" class="text-sm font-medium {{ $navDark ? 'text-slate-300 hover:text-white' : 'text-stone-600 hover:text-stone-900' }}">Iniciar sesión</a>
                        <a href="{{ route('register') }}" class="text-sm font-medium px-4 py-2 rounded-full {{ $navDark ? 'bg-emerald-600 text-white border border-emerald-500/50 hover:bg-emerald-500 shadow-sm shadow-emerald-900/30' : 'bg-amber-600 text-white hover:bg-amber-700 shadow-sm' }}">Crear cuenta</a>
                    </div>
                @endauth
            </div>

            <div class="-me-2 flex items-center sm:hidden">
                <button type="button" @click="open = ! open" class="inline-flex items-center justify-center min-h-[44px] min-w-[44px] p-2 rounded-lg transition duration-150 ease-in-out {{ $navDark ? 'text-slate-200 hover:text-white hover:bg-white/10 active:scale-95' : 'text-stone-400 hover:text-stone-600 hover:bg-stone-100 active:scale-95' }}" aria-expanded="false" aria-label="Menú">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    {{-- Menú móvil --}}
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden border-t {{ $navDark ? 'border-white/10 bg-[#0b1120]/95' : 'border-stone-100 bg-white' }}">
        <div class="space-y-1 px-4 py-3">
            <x-responsive-nav-link :dark="$navDark" :href="route('dashboard')" :active="$isWall && ! $feedFollowing">
                FYP
            </x-responsive-nav-link>
            <x-responsive-nav-link :dark="$navDark" :href="route('dashboard', ['following' => 1])" :active="$isWall && $feedFollowing">
                Siguiendo
            </x-responsive-nav-link>
        </div>

        @auth
            <div class="border-t {{ $navDark ? 'border-white/10' : 'border-stone-100' }} px-4 py-4 flex items-center gap-3">
                <img src="{{ Auth::user()->profile_photo_url }}" alt="" class="h-10 w-10 rounded-full object-cover border {{ $navDark ? 'border-white/20' : 'border-stone-200' }}" />
                <div class="min-w-0 flex-1">
                    <div class="font-medium truncate {{ $navDark ? 'text-white' : 'text-stone-900' }}">{{ trim(Auth::user()->first_name.' '.Auth::user()->last_name) }}</div>
                    <div class="text-sm truncate {{ $navDark ? 'text-slate-400' : 'text-stone-500' }}">{{ Auth::user()->email }}</div>
                </div>
            </div>
            <div class="px-4 pb-4 space-y-1">
                <x-responsive-nav-link :href="route('dashboard')">Ir al muro</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('profile.edit')">Perfil</x-responsive-nav-link>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <x-responsive-nav-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">Cerrar sesión</x-responsive-nav-link>
                </form>
            </div>
        @else
            <div class="border-t {{ $navDark ? 'border-white/10' : 'border-stone-100' }} px-4 py-4 space-y-2">
                <x-responsive-nav-link :href="route('login')">Iniciar sesión</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('register')">Crear cuenta</x-responsive-nav-link>
            </div>
        @endauth
    </div>
</nav>
