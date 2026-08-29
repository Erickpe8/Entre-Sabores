<x-guest-layout title="Ingresar | Entre Sabores">
    <x-auth.card-header
        title="¡Bienvenido de nuevo!"
        subtitle="Ingresa para continuar."
    />

    <form method="POST" action="{{ route('login') }}" class="auth-form" novalidate>
        @csrf

        <div class="auth-field">
            <x-input-label for="email" value="Correo electrónico" />
            <div class="relative">
                <span class="auth-input-icon">
                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M2.5 6.5 10 11l7.5-4.5M3 15h14a1 1 0 0 0 1-1V6a1 1 0 0 0-1-1H3a1 1 0 0 0-1 1v8a1 1 0 0 0 1 1Z"/></svg>
                </span>
                <input id="email" name="email" type="email" value="{{ old('email') }}" placeholder="Ingresa tu correo" required autofocus autocomplete="username" class="input auth-input-with-icon">
            </div>
        </div>

        <div class="auth-field">
            <x-input-label for="password" value="Contraseña" />
            <div class="relative">
                <span class="auth-input-icon">
                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 8V6a4 4 0 1 1 8 0v2m-9 0h10a1 1 0 0 1 1 1v6a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V9a1 1 0 0 1 1-1Z"/></svg>
                </span>
                <input id="password" name="password" type="password" required autocomplete="current-password" class="input auth-input-with-icon">
            </div>
        </div>

        <div class="auth-field flex items-center justify-between gap-3">
            <label for="remember_me" class="inline-flex items-center gap-2 text-caption text-secondary">
                <input id="remember_me" type="checkbox" name="remember" class="h-4 w-4 rounded-base border-line-strong text-warm focus:ring-warm/30">
                Recuérdame
            </label>

            @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}" class="text-link shrink-0 text-caption">¿Olvidaste tu contraseña?</a>
            @endif
        </div>

        <div class="auth-actions">
            <x-primary-button class="w-full">Ingresar</x-primary-button>
        </div>

        <p class="auth-form-footer">
            ¿No tienes cuenta?
            <a href="{{ route('register') }}" class="text-link">Regístrate</a>
        </p>
    </form>
</x-guest-layout>
