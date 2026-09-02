<x-guest-layout
    title="Registrar usuario | Entre Sabores"
    :meta-description="config('seo.auth_descriptions.register')"
>
    @php
        $registerStep = 1;

        if ($errors->any()) {
            if ($errors->has('profile_photo')) {
                $registerStep = 4;
            } elseif ($errors->hasAny(['description', 'country'])) {
                $registerStep = 2;
            } elseif ($errors->hasAny(['password', 'password_confirmation'])) {
                $registerStep = 3;
            } elseif ($errors->hasAny(['first_name', 'last_name', 'email'])) {
                $registerStep = 1;
            }
        }
    @endphp

    <x-auth.card-header
        title="Crea tu cuenta"
        subtitle="Únete a la comunidad gastronómica."
    />

    <form
        id="register-form"
        method="POST"
        action="{{ route('register') }}"
        enctype="multipart/form-data"
        class="auth-form"
        novalidate
        data-auth-wizard
        data-register-initial-step="{{ $registerStep - 1 }}"
    >
        @csrf

        <div class="auth-register-progress" id="register-progress" aria-hidden="true">
            @for ($i = 1; $i <= 4; $i++)
                <span
                    data-step-dot
                    class="auth-register-progress__dot {{ $i <= $registerStep ? 'is-active' : '' }} {{ $i < $registerStep ? 'is-complete' : '' }}"
                ></span>
            @endfor
        </div>

        <p class="auth-register-step-label text-caption text-secondary">
            Paso <span id="register-step-current">{{ $registerStep }}</span> de 4
        </p>

        {{-- Paso 1: nombre y correo --}}
        <div
            id="step-1"
            class="register-step space-y-4"
            data-step-fields="first_name,last_name,email"
            @if($registerStep !== 1) hidden @endif
        >
            <div class="grid grid-cols-2 gap-3">
                <div class="auth-field !mt-0">
                    <x-input-label for="first_name" value="Nombre" />
                    <input id="first_name" class="input" type="text" name="first_name" value="{{ old('first_name') }}" required autocomplete="given-name" />
                </div>
                <div class="auth-field !mt-0">
                    <x-input-label for="last_name" value="Apellido" />
                    <input id="last_name" class="input" type="text" name="last_name" value="{{ old('last_name') }}" required autocomplete="family-name" />
                </div>
            </div>

            <div class="auth-field !mt-0">
                <x-input-label for="email" value="Correo electrónico" />
                <input id="email" class="input" type="email" name="email" value="{{ old('email') }}" required autocomplete="username" />
            </div>

            <div class="auth-actions">
                <button type="button" data-register-next class="btn btn-primary w-full">Continuar</button>
            </div>
        </div>

        {{-- Paso 2: descripción y país --}}
        <div
            id="step-2"
            class="register-step space-y-4"
            data-step-fields="description,country"
            data-country-hint-url="{{ route('register.country-hint') }}"
            @if($registerStep !== 2) hidden @endif
        >
            <div class="auth-field !mt-0">
                <x-input-label for="description" value="Descripción (opcional)" />
                <textarea
                    id="description"
                    name="description"
                    placeholder="Cuéntanos sobre ti..."
                    class="input h-16 resize-none"
                >{{ old('description') }}</textarea>
            </div>

            <div class="auth-field !mt-0">
                <x-input-label for="country" value="País" />
                <select id="country" name="country" class="input" required>
                    <option value="">Selecciona tu país</option>
                    <option value="Colombia" @selected(old('country') === 'Colombia')>Colombia</option>
                    <option value="México" @selected(old('country') === 'México')>México</option>
                    <option value="Argentina" @selected(old('country') === 'Argentina')>Argentina</option>
                    <option value="Chile" @selected(old('country') === 'Chile')>Chile</option>
                    <option value="Perú" @selected(old('country') === 'Perú')>Perú</option>
                    <option value="Ecuador" @selected(old('country') === 'Ecuador')>Ecuador</option>
                    <option value="Venezuela" @selected(old('country') === 'Venezuela')>Venezuela</option>
                    <option value="Bolivia" @selected(old('country') === 'Bolivia')>Bolivia</option>
                    <option value="Paraguay" @selected(old('country') === 'Paraguay')>Paraguay</option>
                    <option value="Uruguay" @selected(old('country') === 'Uruguay')>Uruguay</option>
                </select>
            </div>

            <div class="auth-actions flex gap-3">
                <button type="button" data-register-back class="btn btn-secondary w-1/2">Atrás</button>
                <button type="button" data-register-next class="btn btn-primary w-1/2">Continuar</button>
            </div>
        </div>

        {{-- Paso 3: contraseña --}}
        <div
            id="step-3"
            class="register-step space-y-4"
            data-step-fields="password,password_confirmation"
            @if($registerStep !== 3) hidden @endif
        >
            <div class="grid grid-cols-2 gap-3">
                <div class="auth-field !mt-0">
                    <x-input-label for="password" value="Contraseña" />
                    <input id="password" class="input" type="password" name="password" required autocomplete="new-password" />
                </div>
                <div class="auth-field !mt-0">
                    <x-input-label for="password_confirmation" value="Confirmar" />
                    <input id="password_confirmation" class="input" type="password" name="password_confirmation" required autocomplete="new-password" />
                </div>
            </div>

            <div class="auth-actions flex gap-3">
                <button type="button" data-register-back class="btn btn-secondary w-1/2">Atrás</button>
                <button type="button" data-register-next class="btn btn-primary w-1/2">Continuar</button>
            </div>
        </div>

        {{-- Paso 4: foto --}}
        <div
            id="step-4"
            class="register-step space-y-3"
            data-step-fields="profile_photo"
            @if($registerStep !== 4) hidden @endif
        >
            <div class="auth-photo-picker auth-photo-picker--compact">
                <img id="preview" alt="Vista previa de tu foto" class="auth-photo-picker__preview hidden">

                <button type="button" id="openEditor" class="auth-photo-picker__trigger">
                    <span class="auth-photo-picker__icon" aria-hidden="true">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 0 1 5.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 0 0-1.134-.175 2.31 2.31 0 0 1-1.64-1.055l-.822-1.316a2.192 2.192 0 0 0-1.736-1.039 48.774 48.774 0 0 0-5.232 0 2.192 2.192 0 0 0-1.736 1.039l-.821 1.316Z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0Z"/>
                        </svg>
                    </span>
                    <span class="auth-photo-picker__label">Seleccionar y editar foto</span>
                    <span class="auth-photo-picker__hint">JPG, PNG o WebP · máx. 2 MB</span>
                </button>
            </div>

            <input type="hidden" id="profile_photo_base64" name="profile_photo_base64">
            <input id="profile_photo" type="file" name="profile_photo" class="hidden" required>

            <div class="auth-actions flex gap-3">
                <button type="button" data-register-back class="btn btn-secondary w-1/2">Atrás</button>
                <button type="submit" class="btn btn-primary w-1/2">Registrarse</button>
            </div>
        </div>

        <p class="auth-form-footer">
            ¿Ya tienes cuenta?
            <a class="text-link" href="{{ route('login') }}">Inicia sesión</a>
        </p>
    </form>

    <x-avatar-cropper
        mode="register"
        crop-source="dynamic"
        preview-id="preview"
        base64-input-id="profile_photo_base64"
        open-button-id="openEditor"
        crop-image-id="imageCrop"
        modal-id="modalCrop"
        data-transfer-input-id="profile_photo"
        form-id="register-form"
        :step-ids="['step-1', 'step-2', 'step-3', 'step-4']"
    />
</x-guest-layout>
