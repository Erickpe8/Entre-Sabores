<section>
    <header>
        <h2 class="text-lg font-semibold text-white">
            Actualizar contraseña
        </h2>

        <p class="mt-1 text-sm text-slate-400">
            Usa una contraseña larga y aleatoria para mantener tu cuenta segura.
        </p>
    </header>

    <form method="post" action="{{ route('password.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('put')

        <div>
            <label for="update_password_current_password" class="block text-sm font-medium text-gray-300 mb-1">Contraseña actual</label>
            <x-text-input id="update_password_current_password" name="current_password" type="password" class="input mt-0" autocomplete="current-password" />
            <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-2" />
        </div>

        <div>
            <label for="update_password_password" class="block text-sm font-medium text-gray-300 mb-1">Nueva contraseña</label>
            <x-text-input id="update_password_password" name="password" type="password" class="input mt-0" autocomplete="new-password" />
            <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-2" />
        </div>

        <div>
            <label for="update_password_password_confirmation" class="block text-sm font-medium text-gray-300 mb-1">Confirmar contraseña</label>
            <x-text-input id="update_password_password_confirmation" name="password_confirmation" type="password" class="input mt-0" autocomplete="new-password" />
            <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center gap-4">
            <button
                type="submit"
                class="inline-flex items-center rounded-xl px-5 py-2.5 bg-gradient-to-r from-green-400 to-blue-500 text-white text-sm font-semibold shadow-lg shadow-green-900/30 hover:opacity-95 transition"
            >
                Guardar contraseña
            </button>

            @if (session('status') === 'password-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-green-400"
                >{{ __('Guardado.') }}</p>
            @endif
        </div>
    </form>
</section>
