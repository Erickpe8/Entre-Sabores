<x-guest-layout title="Restablecer contraseña | Entre Sabores">
    <x-auth.card-header
        title="Crear nueva contraseña"
        subtitle="Define una contraseña segura para proteger tu cuenta."
    />

    <form method="POST" action="{{ route('password.store') }}" class="auth-form" novalidate>
        @csrf

        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <div class="auth-field">
            <x-input-label for="email" value="Correo electrónico" />
            <x-text-input id="email" type="email" name="email" :value="old('email', $request->email)" required autofocus autocomplete="username" />
        </div>

        <div class="auth-field">
            <x-input-label for="password" value="Contraseña" />
            <x-text-input id="password" type="password" name="password" required autocomplete="new-password" />
        </div>

        <div class="auth-field">
            <x-input-label for="password_confirmation" value="Confirmar contraseña" />
            <x-text-input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password" />
        </div>

        <div class="auth-actions">
            <x-primary-button class="w-full">Restablecer contraseña</x-primary-button>
        </div>
    </form>
</x-guest-layout>
