<x-guest-layout title="Verificar correo | Entre Sabores">
    <x-auth.card-header
        title="Verifica tu correo electrónico"
        subtitle="Te enviamos un enlace de verificación. Ábrelo para activar tu cuenta y continuar."
    />

    <div class="auth-form">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <form method="POST" action="{{ route('verification.send') }}">
                @csrf
                <x-primary-button>Reenviar correo de verificación</x-primary-button>
            </form>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="text-link text-caption">
                    Cerrar sesión
                </button>
            </form>
        </div>
    </div>
</x-guest-layout>
