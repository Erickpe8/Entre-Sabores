@php
    $isWall = request()->routeIs('dashboard');
    $feedFollowing = $isWall && request()->boolean('following');
@endphp

@if ($isWall)
    <button
        type="button"
        data-navbar-feed="fyp"
        class="navbar-feed-tab {{ ! $feedFollowing ? 'navbar-feed-tab--active' : '' }}"
        aria-pressed="{{ ! $feedFollowing ? 'true' : 'false' }}"
        @if (! $feedFollowing) aria-current="page" @endif
    >
        FYP
    </button>
    <button
        type="button"
        data-navbar-feed="following"
        class="navbar-feed-tab {{ $feedFollowing ? 'navbar-feed-tab--active' : '' }}"
        aria-pressed="{{ $feedFollowing ? 'true' : 'false' }}"
        @if ($feedFollowing) aria-current="page" @endif
        title="{{ auth()->check() ? 'Solo cuentas que sigues' : 'Inicia sesión' }}"
    >
        Siguiendo
    </button>
@else
    <a
        href="{{ route('dashboard') }}"
        class="navbar-feed-tab {{ request()->routeIs('dashboard') && ! request()->boolean('following') ? 'navbar-feed-tab--active' : '' }}"
        @if (request()->routeIs('dashboard') && ! request()->boolean('following')) aria-current="page" @endif
    >
        FYP
    </a>
    <a
        href="{{ route('dashboard', ['following' => 1]) }}"
        class="navbar-feed-tab {{ request()->routeIs('dashboard') && request()->boolean('following') ? 'navbar-feed-tab--active' : '' }}"
        @if (request()->routeIs('dashboard') && request()->boolean('following')) aria-current="page" @endif
        title="{{ auth()->check() ? '' : 'Inicia sesión' }}"
    >
        Siguiendo
    </a>
@endif
