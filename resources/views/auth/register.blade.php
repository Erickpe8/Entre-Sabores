<x-guest-layout title="Registrar usuario | Entre Sabores">
    <link href="https://unpkg.com/cropperjs@1.6.2/dist/cropper.min.css" rel="stylesheet">
    <style>
        .cropper-view-box,
        .cropper-face {
            border-radius: 50%;
        }

        .cropper-view-box {
            outline: 2px solid #22c55e;
        }

        .cropper-modal {
            background: rgba(0, 0, 0, 0.7);
        }

        .cropper-canvas {
            background: #000;
        }

        .custom-scroll {
            overflow-y: auto;
            scrollbar-width: none;
            -ms-overflow-style: none;
            scroll-behavior: smooth;
        }

        .custom-scroll::-webkit-scrollbar {
            display: none;
        }
    </style>

    <div class="mb-5">
        <h1 class="text-4xl font-extrabold text-white leading-tight">
            Crea tu cuenta
        </h1>
        <p class="mt-2 text-sm text-slate-400">Únete a la comunidad gastronómica.</p>
    </div>

    <form id="register-form" method="POST" action="{{ route('register') }}" enctype="multipart/form-data" class="space-y-4">
        @csrf

        <div id="step-1" class="space-y-4">
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label for="first_name" class="mb-2 block text-sm font-semibold text-slate-100">Nombre</label>
                    <input id="first_name" class="w-full px-3 py-2.5 text-sm rounded-lg bg-white/10 border border-white/20 text-white focus:outline-none focus:ring-2 focus:ring-green-400" type="text" name="first_name" value="{{ old('first_name') }}" required autofocus autocomplete="given-name" />
                    <x-input-error :messages="$errors->get('first_name')" class="mt-2 text-sm" />
                </div>

                <div>
                    <label for="last_name" class="mb-2 block text-sm font-semibold text-slate-100">Apellido</label>
                    <input id="last_name" class="w-full px-3 py-2.5 text-sm rounded-lg bg-white/10 border border-white/20 text-white focus:outline-none focus:ring-2 focus:ring-green-400" type="text" name="last_name" value="{{ old('last_name') }}" required autocomplete="family-name" />
                    <x-input-error :messages="$errors->get('last_name')" class="mt-2 text-sm" />
                </div>
            </div>

            <div>
                <label for="email" class="mb-2 block text-sm font-semibold text-slate-100">Correo electrónico</label>
                <input id="email" class="w-full px-3 py-2.5 text-sm rounded-lg bg-white/10 border border-white/20 text-white focus:outline-none focus:ring-2 focus:ring-green-400" type="email" name="email" value="{{ old('email') }}" required autocomplete="username" />
                <x-input-error :messages="$errors->get('email')" class="mt-2 text-sm" />
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label for="password" class="mb-2 block text-sm font-semibold text-slate-100">Contraseña</label>
                    <input id="password" class="w-full px-3 py-2.5 text-sm rounded-lg bg-white/10 border border-white/20 text-white focus:outline-none focus:ring-2 focus:ring-green-400" type="password" name="password" required autocomplete="new-password" />
                    <x-input-error :messages="$errors->get('password')" class="mt-2 text-sm" />
                </div>

                <div>
                    <label for="password_confirmation" class="mb-2 block text-sm font-semibold text-slate-100">Confirmar contraseña</label>
                    <input id="password_confirmation" class="w-full px-3 py-2.5 text-sm rounded-lg bg-white/10 border border-white/20 text-white focus:outline-none focus:ring-2 focus:ring-green-400" type="password" name="password_confirmation" required autocomplete="new-password" />
                    <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2 text-sm" />
                </div>
            </div>

            <div>
                <label for="country" class="mb-2 block text-sm font-semibold text-slate-100">País</label>
                <select id="country" name="country" class="w-full rounded-lg bg-gray-800 border border-gray-600 px-3 py-2.5 text-sm text-white focus:outline-none focus:ring-2 focus:ring-green-400" required>
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
                <x-input-error :messages="$errors->get('country')" class="mt-2 text-sm" />
            </div>

            <button id="go-step-2" type="button" class="w-full py-2.5 mt-2 rounded-lg text-sm bg-gradient-to-r from-green-400 to-blue-500 text-white font-semibold hover:opacity-90 transition">
                Continuar
            </button>
        </div>

        <div id="step-2" class="hidden space-y-5 max-w-md">
            <button type="button" id="openEditor"
                class="w-full py-3 rounded-xl bg-gradient-to-r from-green-400 to-blue-500 text-white font-semibold hover:opacity-90 transition">
                Seleccionar y editar foto
            </button>

            <div class="flex justify-center">
                <img id="preview"
                    alt="Vista previa"
                    class="w-24 h-24 rounded-full object-cover hidden border-2 border-white">
            </div>

            <input type="hidden" id="profile_photo_base64" name="profile_photo_base64">
            <input id="profile_photo" type="file" name="profile_photo" class="hidden" required>
            <x-input-error :messages="$errors->get('profile_photo')" class="mt-2 text-sm" />

            <div>
                <label for="description" class="mb-2 block text-sm font-semibold text-slate-100">Descripción</label>
                <textarea
                    id="description"
                    name="description"
                    placeholder="Cuéntanos sobre ti..."
                    class="w-full rounded-xl bg-gray-800 border border-gray-600 px-4 py-3 text-white resize-none h-24 custom-scroll focus:outline-none focus:ring-2 focus:ring-green-400">{{ old('description') }}</textarea>
                <x-input-error :messages="$errors->get('description')" class="mt-2 text-sm" />
            </div>

            <div class="flex items-center gap-3">
                <button id="back-step-1" type="button" class="w-1/2 py-3 rounded-xl border border-white/30 bg-transparent text-white font-medium hover:bg-white/10 transition">
                    Atrás
                </button>
                <button type="submit" class="w-1/2 py-3 rounded-xl bg-gradient-to-r from-green-400 to-blue-500 text-white font-semibold hover:opacity-90 transition">
                    Registrarse
                </button>
            </div>
        </div>

        <p class="text-center text-sm text-slate-300">
            ¿Ya tienes cuenta?
            <a class="font-semibold text-cyan-300 hover:text-cyan-200" href="{{ route('login') }}">Inicia sesión</a>
        </p>
    </form>

    <div id="modalCrop" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/80 backdrop-blur-sm px-4">
        <div class="bg-[#0f172a] rounded-2xl w-full max-w-4xl p-6 shadow-2xl">
            <h2 class="text-white text-xl mb-4">Ajusta tu foto</h2>

            <div class="w-full h-[400px] bg-black rounded-xl overflow-hidden flex items-center justify-center">
                <img id="imageCrop" alt="Editor de avatar" class="max-w-full max-h-full object-contain">
            </div>

            <div class="flex justify-end gap-4 mt-6">
                <button id="btnReset" type="button" class="px-4 py-2 bg-gray-600 rounded-lg text-white hover:bg-gray-500 transition">
                    Reintentar
                </button>

                <button id="btnCancel" type="button" class="px-4 py-2 bg-red-500 rounded-lg text-white hover:bg-red-400 transition">
                    Cancelar
                </button>

                <button id="btnApply" type="button" class="px-4 py-2 bg-green-500 rounded-lg text-white hover:bg-green-400 transition">
                    Aplicar
                </button>
            </div>
        </div>
    </div>

    <script src="https://unpkg.com/cropperjs@1.6.2/dist/cropper.min.js"></script>
    <script>
        (() => {
            const form = document.getElementById('register-form');
            const step1 = document.getElementById('step-1');
            const step2 = document.getElementById('step-2');
            const goStep2 = document.getElementById('go-step-2');
            const backStep1 = document.getElementById('back-step-1');

            const step1Fields = ['first_name', 'last_name', 'email', 'password', 'password_confirmation', 'country']
                .map((id) => document.getElementById(id));

            const realInput = document.getElementById('profile_photo');
            const base64Input = document.getElementById('profile_photo_base64');

            const openEditor = document.getElementById('openEditor');
            const modal = document.getElementById('modalCrop');
            const cancelBtn = document.getElementById('btnCancel');
            const resetBtn = document.getElementById('btnReset');
            const applyBtn = document.getElementById('btnApply');
            const imageToEdit = document.getElementById('imageCrop');
            const preview = document.getElementById('preview');

            if (!form || !step1 || !step2 || !goStep2 || !backStep1 || !realInput || !base64Input || !openEditor ||
                !modal || !cancelBtn || !resetBtn || !applyBtn || !imageToEdit || !preview) {
                return;
            }

            let cropper = null;
            let objectUrl = null;
            const CropperConstructor = window.Cropper?.default || window.Cropper;

            const destroyCropper = () => {
                if (cropper) {
                    cropper.destroy();
                    cropper = null;
                }
            };

            const cleanupObjectUrl = () => {
                if (objectUrl) {
                    URL.revokeObjectURL(objectUrl);
                    objectUrl = null;
                }
            };

            const openImageModal = () => {
                modal.classList.remove('hidden');
                modal.classList.add('flex');
            };

            const closeImageModal = () => {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            };

            const applyCoverScale = (extraMargin = 0.2) => {
                const coverScale = getCoverScaleFromImage();
                if (!coverScale) return null;
                const targetScale = coverScale + extraMargin;
                cropper.zoomTo(targetScale);
                return targetScale;
            };

            const getCoverScaleFromImage = () => {
                if (!cropper) return null;
                const containerData = cropper.getContainerData();
                const imageData = cropper.getImageData();
                if (!containerData?.width || !containerData?.height || !imageData?.naturalWidth || !imageData?.naturalHeight) {
                    return null;
                }

                return Math.max(
                    containerData.width / imageData.naturalWidth,
                    containerData.height / imageData.naturalHeight
                );
            };

            goStep2.addEventListener('click', () => {
                const isValid = step1Fields.every((field) => field.reportValidity());
                if (!isValid) return;

                step1.classList.add('hidden');
                step2.classList.remove('hidden');
            });

            backStep1.addEventListener('click', () => {
                step2.classList.add('hidden');
                step1.classList.remove('hidden');
            });

            openEditor.addEventListener('click', () => {
                const input = document.createElement('input');
                input.type = 'file';
                input.accept = 'image/*';

                input.onchange = (event) => {
                    const [file] = event.target.files || [];
                    if (!file) return;

                    destroyCropper();
                    cleanupObjectUrl();

                    objectUrl = URL.createObjectURL(file);
                    imageToEdit.src = objectUrl;
                    openImageModal();

                    imageToEdit.onload = () => {
                        destroyCropper();
                        if (!CropperConstructor) return;

                        cropper = new CropperConstructor(imageToEdit, {
                            aspectRatio: 1,
                            viewMode: 3,
                            dragMode: 'move',
                            autoCropArea: 1,
                            responsive: true,
                            background: false,
                            guides: false,
                            highlight: false,
                            cropBoxMovable: false,
                            cropBoxResizable: false,
                            movable: true,
                            zoomable: true,
                            zoomOnWheel: true,
                            rotatable: false,
                            scalable: false,
                            minCropBoxWidth: 200,
                            minCropBoxHeight: 200,
                        });

                        applyCoverScale(0.2);
                    };
                };

                input.click();
            });

            resetBtn.addEventListener('click', () => {
                if (!cropper) return;
                cropper.reset();
                applyCoverScale(0.2);
            });

            applyBtn.addEventListener('click', () => {
                if (!cropper) return;

                const canvas = cropper.getCroppedCanvas({
                    width: 300,
                    height: 300,
                    imageSmoothingQuality: 'high',
                });
                if (!canvas) return;

                const base64 = canvas.toDataURL('image/jpeg', 0.9);
                base64Input.value = base64;
                preview.src = base64;
                preview.classList.remove('hidden');

                canvas.toBlob((blob) => {
                    if (!blob) return;

                    const file = new File([blob], 'profile-cropped.jpg', { type: 'image/jpeg' });
                    const dt = new DataTransfer();
                    dt.items.add(file);
                    realInput.files = dt.files;

                    closeImageModal();
                    destroyCropper();
                    cleanupObjectUrl();
                }, 'image/jpeg', 0.9);
            });

            cancelBtn.addEventListener('click', () => {
                closeImageModal();
                destroyCropper();
                cleanupObjectUrl();
            });

            form.addEventListener('submit', (event) => {
                if (!realInput.files.length) {
                    event.preventDefault();
                    step1.classList.add('hidden');
                    step2.classList.remove('hidden');
                    openEditor.focus();
                }
            });
        })();
    </script>
</x-guest-layout>
