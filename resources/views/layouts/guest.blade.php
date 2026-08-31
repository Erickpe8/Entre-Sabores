<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    @php
        $authIllustration = match (true) {
            request()->routeIs('register') => 'auth-register-join-community',
            request()->routeIs('login') => 'auth-login-welcome-back',
            default => 'auth-welcome-food-world',
        };
        $isRegisterAuth = request()->routeIs('register');
        $isLoginAuth = request()->routeIs('login');
    @endphp
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $title ?? config('app.name', 'Entre Sabores') }}</title>
        <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body @class([
        'auth-page font-sans antialiased',
        'auth-page--register' => $isRegisterAuth,
        'auth-page--login' => $isLoginAuth,
    ])>
        <x-ui.alert-stack class="top-20" />

        <header class="auth-page__header">
            <a href="{{ route('welcome') }}" class="auth-logo" aria-label="Entre Sabores, inicio">
                <x-ui.app-logo class="h-10 w-10 shrink-0" />
                <span class="auth-logo__title">Entre <span class="auth-logo__accent">Sabores</span></span>
            </a>
        </header>

        <main class="auth-page__main">
            @unless ($isLoginAuth)
            <div class="auth-illustration auth-illustration--mobile" aria-hidden="true">
                <div class="auth-illustration__glow">
                    <div class="auth-illustration__glow-inner scale-75"></div>
                </div>
                <x-ui.illustration
                    :name="$authIllustration"
                    :lazy="false"
                    :img-class="$isRegisterAuth ? 'auth-illustration__img--compact auth-illustration__img--prominent-compact' : 'auth-illustration__img--compact'"
                />
            </div>
            @endunless

            <div class="auth-page__grid">
                <section class="auth-page__form-col">
                    <div class="auth-card">
                        {{ $slot }}
                    </div>
                </section>

                <aside class="auth-illustration auth-illustration--desktop" aria-hidden="true">
                    <div class="auth-illustration__glow">
                        <div class="auth-illustration__glow-inner"></div>
                    </div>
                <x-ui.illustration
                    :name="$authIllustration"
                    :lazy="false"
                    :img-class="match (true) {
                        $isLoginAuth => 'auth-illustration__img auth-illustration__img--login',
                        $isRegisterAuth => 'auth-illustration__img auth-illustration__img--prominent',
                        default => 'auth-illustration__img scale-105',
                    }"
                />
                </aside>
            </div>
        </main>
    </body>
</html>
