<x-guest-layout title="Recuperar contraseña | Entre Sabores">
    <x-auth.card-header
        title="Recuperar contraseña"
        subtitle="Ingresa tu correo electrónico y te enviaremos un enlace para crear una nueva contraseña."
    />

    <form method="POST" action="{{ route('password.email') }}" class="auth-form" novalidate>
        @csrf

        <div class="auth-field">
            <x-input-label for="email" value="Correo electrónico" />
            <x-text-input id="email" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
        </div>

        <div class="auth-actions">
            <x-primary-button class="w-full">Enviar enlace de recuperación</x-primary-button>
        </div>

        <p class="auth-form-footer">
            <a href="{{ route('login') }}" class="text-link">Volver a iniciar sesión</a>
        </p>
    </form>
</x-guest-layout>
