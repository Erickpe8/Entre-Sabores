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

<div id="{{ $modalId }}" class="fixed inset-0 z-[60] hidden items-center justify-center bg-black/80 backdrop-blur-sm px-4">
    <div class="bg-[#0f172a] rounded-2xl w-full max-w-4xl p-6 shadow-2xl border border-white/10">
        <h2 class="text-white text-xl mb-4">Ajusta tu foto</h2>

        <div class="w-full h-[400px] bg-black rounded-xl overflow-hidden flex items-center justify-center">
            <img id="{{ $cropImageId }}" alt="Editor de avatar" class="max-w-full max-h-full object-contain">
        </div>

        <div class="flex justify-end gap-4 mt-6">
            <button id="{{ $modalId }}_btnReset" type="button" class="px-4 py-2 bg-gray-600 rounded-lg text-white hover:bg-gray-500 transition">
                Reintentar
            </button>

            <button id="{{ $modalId }}_btnCancel" type="button" class="px-4 py-2 bg-red-500 rounded-lg text-white hover:bg-red-400 transition">
                Cancelar
            </button>

            <button id="{{ $modalId }}_btnApply" type="button" class="px-4 py-2 bg-green-500 rounded-lg text-white hover:bg-green-400 transition">
                Aplicar
            </button>
        </div>
    </div>
</div>

<script src="https://unpkg.com/cropperjs@1.6.2/dist/cropper.min.js"></script>
@php
    $cropperClientConfig = [
        'mode' => $mode,
        'previewId' => $previewId,
        'base64InputId' => $base64InputId,
        'openButtonId' => $openButtonId,
        'cropImageId' => $cropImageId,
        'modalId' => $modalId,
        'cropSource' => $cropSource,
        'cropSourceInputId' => $cropSourceInputId,
        'dataTransferInputId' => $dataTransferInputId,
        'formId' => $formId,
        'stepOneId' => $stepOneId,
        'stepTwoId' => $stepTwoId,
    ];
@endphp
<script>
    (() => {
        const config = @json($cropperClientConfig);

        const {
            mode,
            previewId,
            base64InputId,
            openButtonId,
            cropImageId,
            modalId,
            cropSource,
            cropSourceInputId,
            dataTransferInputId,
            formId,
            stepOneId,
            stepTwoId,
        } = config;

        const openBtn = document.getElementById(openButtonId);
        const base64Input = document.getElementById(base64InputId);
        const preview = document.getElementById(previewId);
        const imageToEdit = document.getElementById(cropImageId);
        const modal = document.getElementById(modalId);
        const cancelBtn = document.getElementById(`${modalId}_btnCancel`);
        const resetBtn = document.getElementById(`${modalId}_btnReset`);
        const applyBtn = document.getElementById(`${modalId}_btnApply`);

        const cropSourceInput = cropSourceInputId ? document.getElementById(cropSourceInputId) : null;
        const dataTransferInput = dataTransferInputId ? document.getElementById(dataTransferInputId) : null;
        const form = formId ? document.getElementById(formId) : null;
        const step1 = stepOneId ? document.getElementById(stepOneId) : null;
        const step2 = stepTwoId ? document.getElementById(stepTwoId) : null;

        const step1Fields = mode === 'register' && step1
            ? ['first_name', 'last_name', 'email', 'password', 'password_confirmation', 'country']
                .map((id) => document.getElementById(id))
            : [];

        const goStep2 = mode === 'register' ? document.getElementById('go-step-2') : null;
        const backStep1 = mode === 'register' ? document.getElementById('back-step-1') : null;

        if (!openBtn || !base64Input || !preview || !imageToEdit || !modal || !cancelBtn || !resetBtn || !applyBtn) {
            return;
        }

        if (mode === 'profile' && cropSource === 'persistent' && !cropSourceInput) {
            return;
        }

        if (mode === 'register' && cropSource === 'dynamic' && !dataTransferInput) {
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

        const loadFileIntoCropper = (file) => {
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

        if (mode === 'register' && goStep2 && step1 && step2) {
            goStep2.addEventListener('click', () => {
                const isValid = step1Fields.every((field) => field && field.reportValidity());
                if (!isValid) return;

                step1.classList.add('hidden');
                step2.classList.remove('hidden');
            });
        }

        if (mode === 'register' && backStep1 && step1 && step2) {
            backStep1.addEventListener('click', () => {
                step2.classList.add('hidden');
                step1.classList.remove('hidden');
            });
        }

        openBtn.addEventListener('click', () => {
            if (cropSource === 'persistent' && cropSourceInput) {
                cropSourceInput.click();
                return;
            }

            const input = document.createElement('input');
            input.type = 'file';
            input.accept = 'image/*';

            input.onchange = (event) => {
                const [file] = event.target.files || [];
                loadFileIntoCropper(file);
            };

            input.click();
        });

        if (cropSource === 'persistent' && cropSourceInput) {
            cropSourceInput.addEventListener('change', (event) => {
                const [file] = event.target.files || [];
                loadFileIntoCropper(file);
                cropSourceInput.value = '';
            });
        }

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

            if (mode === 'profile') {
                closeImageModal();
                destroyCropper();
                cleanupObjectUrl();
                return;
            }

            canvas.toBlob((blob) => {
                if (!blob || !dataTransferInput) return;

                const file = new File([blob], 'profile-cropped.jpg', { type: 'image/jpeg' });
                const dt = new DataTransfer();
                dt.items.add(file);
                dataTransferInput.files = dt.files;

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

        if (mode === 'register' && form && dataTransferInput && step1 && step2 && openBtn) {
            form.addEventListener('submit', (event) => {
                if (!dataTransferInput.files.length) {
                    event.preventDefault();
                    step1.classList.add('hidden');
                    step2.classList.remove('hidden');
                    openBtn.focus();
                }
            });
        }
    })();
</script>
