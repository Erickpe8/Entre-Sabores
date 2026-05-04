<section class="space-y-6">
    <header>
        <h2 class="text-lg font-semibold text-white">
            Eliminar cuenta
        </h2>

        <p class="mt-1 text-sm text-slate-400">
            Una vez eliminada tu cuenta, todos tus datos se borrarán de forma permanente. Descarga cualquier información que quieras conservar antes de continuar.
        </p>
    </header>

    <x-danger-button type="button" data-open-modal="confirm-user-deletion">
        Eliminar cuenta
    </x-danger-button>

    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <form method="post" action="{{ route('settings.profile.destroy') }}" class="p-6">
            @csrf
            @method('delete')

            <h2 class="text-lg font-medium text-gray-900">
                ¿Seguro que quieres eliminar tu cuenta?
            </h2>

            <p class="mt-1 text-sm text-gray-600">
                Esta acción no se puede deshacer. Escribe tu contraseña para confirmar que deseas eliminar tu cuenta de forma permanente.
            </p>

            <div class="mt-6">
                <x-input-label for="password" value="Contraseña" class="sr-only" />

                <x-text-input
                    id="password"
                    name="password"
                    type="password"
                    class="mt-1 block w-3/4 rounded-lg border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500"
                    placeholder="Contraseña"
                />

                <x-input-error :messages="$errors->userDeletion->get('password')" class="mt-2" />
            </div>

            <div class="mt-6 flex justify-end">
                <x-secondary-button type="button" data-close-modal="confirm-user-deletion">
                    Cancelar
                </x-secondary-button>

                <x-danger-button class="ms-3">
                    Eliminar cuenta
                </x-danger-button>
            </div>
        </form>
    </x-modal>
</section>
