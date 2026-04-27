<x-app-layout title="{{ __('Mi perfil') }} — {{ config('app.name') }}">
    <div class="min-h-screen bg-gradient-to-br from-[#020617] via-[#0f172a] to-[#022c22] pb-16">
        <div class="max-w-7xl mx-auto px-6 py-10 grid lg:grid-cols-3 gap-8">
            {{-- Sidebar --}}
            <aside class="space-y-6 lg:self-start sticky top-24">
                <div class="bg-white/5 backdrop-blur-xl rounded-2xl p-6 shadow-xl border border-white/10 hover:scale-[1.01] transition">
                    <div class="flex flex-col items-center text-center space-y-3">
                        <img
                            id="avatarPreview"
                            src="{{ $user->profile_photo_url }}"
                            alt=""
                            class="w-28 h-28 rounded-full border-2 border-green-400 object-cover shrink-0"
                        >

                        <button
                            type="button"
                            id="openAvatarModal"
                            class="text-sm text-green-400 hover:underline focus:outline-none focus:ring-2 focus:ring-green-400/50 rounded"
                        >
                            Cambiar foto
                        </button>

                        <input type="file" id="avatarInput" accept="image/*" class="sr-only" tabindex="-1" autocomplete="off">

                        <div class="space-y-1">
                            <h3 class="text-white font-semibold text-lg leading-tight">
                                {{ $user->first_name }} {{ $user->last_name }}
                            </h3>

                            <div class="flex items-center justify-center gap-2 text-gray-400 text-sm">
                                <i data-lucide="map-pin" class="w-4 h-4 text-green-400 shrink-0" aria-hidden="true"></i>
                                <span>{{ $user->country ?? '—' }}</span>
                            </div>
                        </div>

                        <div class="mt-3 w-full space-y-3 text-sm text-gray-400 border-t border-white/10 pt-4">
                            @if ($user->birthdate)
                                <div class="flex items-center justify-center gap-2">
                                    <i data-lucide="cake" class="w-4 h-4 text-green-400 shrink-0" aria-hidden="true"></i>
                                    <span class="text-gray-300">{{ $user->birthdate->age }} años</span>
                                </div>
                            @endif

                            <div class="flex items-center justify-center gap-2 text-center">
                                <i data-lucide="calendar" class="w-4 h-4 text-green-400 shrink-0" aria-hidden="true"></i>
                                <span>
                                    Miembro desde
                                    <span class="text-gray-300 font-medium">
                                        {{ $user->created_at->locale(app()->getLocale())->translatedFormat('F Y') }}
                                    </span>
                                </span>
                            </div>
                        </div>

                        @php
                            $preferenceIcons = [
                                'Amante del vino' => 'glass-water',
                                'Café lover' => 'coffee',
                                'Comida rápida' => 'pizza',
                                'Gastronomía gourmet' => 'utensils',
                                'Street food' => 'sandwich',
                                'Postres' => 'cake',
                                'Comida tradicional' => 'soup',
                                'Explorador culinario' => 'compass',
                            ];
                        @endphp

                        @if ($user->preferences && count($user->preferences))
                            <div class="mt-3 flex flex-wrap gap-2 justify-center w-full">
                                @foreach ($user->preferences as $pref)
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 text-xs rounded-full bg-green-400/20 text-green-300 border border-green-400/30">
                                        <i data-lucide="{{ $preferenceIcons[$pref] ?? 'star' }}" class="w-3.5 h-3.5 text-green-400 shrink-0" aria-hidden="true"></i>
                                        <span>{{ $pref }}</span>
                                    </span>
                                @endforeach
                            </div>
                        @endif

                        <div class="mt-3 flex flex-wrap gap-4 justify-center items-center w-full border-t border-white/10 pt-4">
                            @if ($user->instagram)
                                <a
                                    href="https://www.instagram.com/{{ $user->instagram }}/"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="flex items-center gap-2 text-pink-400 hover:scale-105 transition"
                                >
                                    <i class="fab fa-instagram text-lg" aria-hidden="true"></i>
                                    <span class="text-sm">{{ '@'.$user->instagram }}</span>
                                </a>
                            @endif

                            @if ($user->linkedin)
                                <a
                                    href="https://www.linkedin.com/in/{{ $user->linkedin }}/"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="flex items-center gap-2 text-sky-400 hover:scale-105 transition"
                                >
                                    <i class="fab fa-linkedin text-lg" aria-hidden="true"></i>
                                    <span class="text-sm">{{ $user->linkedin }}</span>
                                </a>
                            @endif

                            @if (! $user->instagram && ! $user->linkedin)
                                <p class="text-xs text-gray-500 leading-snug max-w-[220px]">
                                    Añade Instagram o LinkedIn en el formulario para mostrar enlaces aquí.
                                </p>
                            @endif
                        </div>
                    </div>
                </div>
            </aside>

            {{-- Contenido --}}
            <main class="lg:col-span-2 space-y-6">
                <form
                    method="post"
                    action="{{ route('profile.update') }}"
                    class="space-y-6"
                >
                    @csrf
                    @method('patch')

                    <input type="hidden" name="profile_edit_form" value="1">

                    <input type="hidden" name="profile_photo_base64" id="profile_photo_base64" value="">
                    <x-input-error class="mt-2" :messages="$errors->get('profile_photo_base64')" />

                    {{-- Información personal --}}
                    <div class="bg-white/5 backdrop-blur rounded-2xl p-6 space-y-5 border border-white/10 hover:scale-[1.01] transition shadow-lg shadow-black/20">
                        <h2 class="text-white text-lg font-semibold">
                            Información personal
                        </h2>

                        <div class="grid md:grid-cols-2 gap-4">
                            <div>
                                <label for="first_name" class="block text-sm font-medium text-gray-300 mb-1">Nombre</label>
                                <input
                                    id="first_name"
                                    name="first_name"
                                    type="text"
                                    class="input"
                                    value="{{ old('first_name', $user->first_name) }}"
                                    required
                                    autocomplete="given-name"
                                >
                                <x-input-error class="mt-2" :messages="$errors->get('first_name')" />
                            </div>
                            <div>
                                <label for="last_name" class="block text-sm font-medium text-gray-300 mb-1">Apellido</label>
                                <input
                                    id="last_name"
                                    name="last_name"
                                    type="text"
                                    class="input"
                                    value="{{ old('last_name', $user->last_name) }}"
                                    required
                                    autocomplete="family-name"
                                >
                                <x-input-error class="mt-2" :messages="$errors->get('last_name')" />
                            </div>
                        </div>

                        <div>
                            <label for="email_display" class="block text-sm font-medium text-gray-300 mb-1">Correo electrónico</label>
                            <input
                                id="email_display"
                                type="email"
                                class="input opacity-70 cursor-not-allowed"
                                value="{{ old('email', $user->email) }}"
                                disabled
                                autocomplete="username"
                            >
                            <input type="hidden" name="email" value="{{ old('email', $user->email) }}">
                            <x-input-error class="mt-2" :messages="$errors->get('email')" />
                        </div>

                        <div>
                            <label for="birthdate" class="block text-sm font-medium text-gray-300 mb-1">Fecha de nacimiento</label>
                            <input
                                id="birthdate"
                                type="date"
                                name="birthdate"
                                value="{{ old('birthdate', $user->birthdate?->format('Y-m-d')) }}"
                                max="{{ now()->subDay()->format('Y-m-d') }}"
                                class="input"
                                autocomplete="bday"
                            >
                            <x-input-error class="mt-2" :messages="$errors->get('birthdate')" />
                            <p class="mt-1 text-xs text-gray-500">Opcional. Sirve para mostrar tu edad en el perfil.</p>
                        </div>
                    </div>

                    {{-- Perfil --}}
                    <div class="bg-white/5 backdrop-blur rounded-2xl p-6 space-y-5 border border-white/10 hover:scale-[1.01] transition shadow-lg shadow-black/20">
                        <h2 class="text-white text-lg font-semibold">
                            Perfil
                        </h2>

                        <div>
                            <label for="country" class="block text-sm font-medium text-gray-300 mb-1">País</label>
                            <select
                                id="country"
                                name="country"
                                class="input"
                                required
                            >
                                <option value="" disabled {{ old('country', $user->country) ? '' : 'selected' }}>Selecciona tu país</option>
                                <option value="Colombia" @selected(old('country', $user->country) === 'Colombia')>Colombia</option>
                                <option value="México" @selected(old('country', $user->country) === 'México')>México</option>
                                <option value="Argentina" @selected(old('country', $user->country) === 'Argentina')>Argentina</option>
                                <option value="Chile" @selected(old('country', $user->country) === 'Chile')>Chile</option>
                                <option value="Perú" @selected(old('country', $user->country) === 'Perú')>Perú</option>
                                <option value="Ecuador" @selected(old('country', $user->country) === 'Ecuador')>Ecuador</option>
                                <option value="Venezuela" @selected(old('country', $user->country) === 'Venezuela')>Venezuela</option>
                                <option value="Bolivia" @selected(old('country', $user->country) === 'Bolivia')>Bolivia</option>
                                <option value="Paraguay" @selected(old('country', $user->country) === 'Paraguay')>Paraguay</option>
                                <option value="Uruguay" @selected(old('country', $user->country) === 'Uruguay')>Uruguay</option>
                            </select>
                            <x-input-error class="mt-2" :messages="$errors->get('country')" />
                        </div>

                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-300 mb-1">Sobre ti</label>
                            <textarea
                                id="description"
                                name="description"
                                rows="5"
                                class="input h-28 resize-none"
                                placeholder="Cuéntanos sobre ti"
                            >{{ old('description', $user->description) }}</textarea>
                            <x-input-error class="mt-2" :messages="$errors->get('description')" />
                        </div>

                        <div class="border-t border-white/10 pt-5 space-y-4">
                            <h3 class="text-white text-sm font-semibold">
                                Preferencias gastronómicas
                            </h3>
                            <p class="text-xs text-gray-500 -mt-2">
                                Elige todas las que te representen (aparecen como etiquetas en tu perfil).
                            </p>

                            <div class="grid grid-cols-2 gap-3">
                                @foreach (\App\Models\User::PREFERENCE_OPTIONS as $pref)
                                    <label class="flex items-center gap-2 cursor-pointer bg-white/5 px-3 py-2 rounded-xl hover:bg-white/10 transition border border-white/10 text-left">
                                        <input
                                            type="checkbox"
                                            name="preferences[]"
                                            value="{{ $pref }}"
                                            @checked(in_array($pref, old('preferences', $user->preferences ?? []), true))
                                            class="accent-green-400 shrink-0 rounded border-gray-600"
                                        >
                                        <span class="text-sm text-gray-200">{{ $pref }}</span>
                                    </label>
                                @endforeach
                            </div>
                            <x-input-error class="mt-2" :messages="$errors->get('preferences')" />
                        </div>

                        <div class="border-t border-white/10 pt-5 space-y-4">
                            <h3 class="text-white text-sm font-semibold">
                                Redes sociales
                            </h3>
                            <p class="text-xs text-gray-500 -mt-2">
                                Solo el usuario (sin @); en LinkedIn puedes pegar la URL del perfil o el slug.
                            </p>

                            <div class="grid md:grid-cols-2 gap-4">
                                <div>
                                    <label for="instagram" class="block text-sm font-medium text-gray-300 mb-1">
                                        <i class="fab fa-instagram text-pink-400 mr-1" aria-hidden="true"></i>
                                        Instagram
                                    </label>
                                    <input
                                        id="instagram"
                                        type="text"
                                        name="instagram"
                                        value="{{ old('instagram', $user->instagram) }}"
                                        placeholder="usuario_instagram"
                                        class="input"
                                        autocomplete="off"
                                    >
                                    <x-input-error class="mt-2" :messages="$errors->get('instagram')" />
                                </div>

                                <div>
                                    <label for="linkedin" class="block text-sm font-medium text-gray-300 mb-1">
                                        <i class="fab fa-linkedin text-sky-400 mr-1" aria-hidden="true"></i>
                                        LinkedIn
                                    </label>
                                    <input
                                        id="linkedin"
                                        type="text"
                                        name="linkedin"
                                        value="{{ old('linkedin', $user->linkedin) }}"
                                        placeholder="usuario-o-url-linkedin"
                                        class="input"
                                        autocomplete="off"
                                    >
                                    <x-input-error class="mt-2" :messages="$errors->get('linkedin')" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <button
                        type="submit"
                        class="w-full py-3 rounded-xl bg-gradient-to-r from-green-400 to-blue-500 text-white font-semibold shadow-lg shadow-green-900/30 hover:opacity-95 transition"
                    >
                        Guardar cambios
                    </button>

                    @if (session('success') || session('status') === 'profile-updated')
                        <p
                            x-data="{ show: true }"
                            x-show="show"
                            x-transition
                            x-init="setTimeout(() => show = false, 2500)"
                            class="text-sm text-center text-green-400"
                        >{{ session('success') ?? __('Guardado.') }}</p>
                    @endif
                </form>

                <div class="bg-white/5 backdrop-blur rounded-2xl p-6 border border-white/10 hover:scale-[1.01] transition shadow-lg shadow-black/20">
                    @include('profile.partials.update-password-form')
                </div>

                <div class="bg-white/5 backdrop-blur rounded-2xl p-6 border border-white/10 hover:scale-[1.01] transition shadow-lg shadow-black/20">
                    @include('profile.partials.delete-user-form')
                </div>
            </main>
        </div>

        <x-avatar-cropper
            mode="profile"
            crop-source="persistent"
            crop-source-input-id="avatarInput"
            preview-id="avatarPreview"
            base64-input-id="profile_photo_base64"
            open-button-id="openAvatarModal"
            crop-image-id="cropperImage"
            modal-id="avatarCropModal"
        />
    </div>
</x-app-layout>
