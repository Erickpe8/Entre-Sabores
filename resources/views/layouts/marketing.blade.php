<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @php
        $seoTitle = trim($__env->yieldContent('title')) ?: config('app.name');
        $seoDescription = trim($__env->yieldContent('meta_description')) ?: config('seo.default_description');
        $seoCanonical = trim($__env->yieldContent('canonical')) ?: null;
        $seoOgImage = trim($__env->yieldContent('og_image')) ?: null;
    @endphp
    <x-seo-head
        :title="$seoTitle"
        :description="$seoDescription"
        :canonical="$seoCanonical ?: url()->current()"
        :og-image="$seoOgImage !== '' ? $seoOgImage : null"
    />
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    @stack('head')
    @vite(['resources/css/app.css', 'resources/js/marketing.js'])
</head>
<body class="min-h-[100dvh] bg-base font-sans antialiased">
    <x-ui.alert-stack />
    <div class="relative flex min-h-[100dvh] flex-col overflow-x-hidden bg-base">
        @include('layouts.partials.marketing-header', ['active' => $active ?? null])

        <main class="relative z-10 es-container flex-1 @if(empty($compactMain)) py-10 sm:py-14 md:py-16 @else py-6 sm:py-8 lg:py-10 @endif">
            @yield('content')
        </main>

        @if (empty($hideFooter))
        <footer class="relative z-10 border-t border-line py-8 text-center text-caption text-muted">
            <p>&copy; {{ date('Y') }} Entre Sabores · <a href="{{ route('welcome') }}" class="text-link">Inicio</a></p>
        </footer>
        @endif
    </div>
</body>
</html>
