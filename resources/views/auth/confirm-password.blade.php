<x-guest-layout title="Confirmar contraseña | Entre Sabores">
    <x-auth.card-header
        title="Confirma tu contraseña"
        subtitle="Esta es una zona segura. Ingresa tu contraseña para continuar."
    />

    <form method="POST" action="{{ route('password.confirm') }}" class="auth-form" novalidate>
        @csrf

        <div class="auth-field">
            <x-input-label for="password" value="Contraseña" />
            <x-text-input id="password" type="password" name="password" required autocomplete="current-password" />
        </div>

        <div class="auth-actions">
            <x-primary-button class="w-full">Confirmar</x-primary-button>
        </div>
    </form>
</x-guest-layout>
