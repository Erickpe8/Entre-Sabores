<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $title ?? config('app.name', 'Entre Sabores') }}</title>
        <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="auth-page font-sans antialiased">
        <x-ui.alert-stack class="top-20" />

        <header class="auth-page__header">
            <a href="{{ route('welcome') }}" class="auth-logo" aria-label="Entre Sabores, inicio">
                <x-ui.app-logo class="h-10 w-10 shrink-0" />
                <span class="auth-logo__title">Entre <span class="auth-logo__accent">Sabores</span></span>
            </a>
        </header>

        <main class="auth-page__main">
            <div class="auth-illustration auth-illustration--mobile" aria-hidden="true">
                <div class="auth-illustration__glow">
                    <div class="auth-illustration__glow-inner scale-75"></div>
                </div>
                <img
                    src="{{ asset('images/hero-gallery.png') }}"
                    alt=""
                    class="auth-illustration__img--compact"
                >
            </div>

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
                    <img
                        src="{{ asset('images/hero-gallery.png') }}"
                        alt=""
                        class="auth-illustration__img scale-110"
                    >
                </aside>
            </div>
        </main>
    </body>
</html>
