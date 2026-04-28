<style>
    .custom-scroll {
        overflow-y: auto;
        scrollbar-width: none;
        -ms-overflow-style: none;
        scroll-behavior: smooth;
    }

    .custom-scroll::-webkit-scrollbar {
        display: none;
    }

    /* Refuerzo local (v1) para asegurar máscara circular visible */
    .avatar-cropper-modal .cropper-crop-box,
    .avatar-cropper-modal .cropper-view-box,
    .avatar-cropper-modal .cropper-face {
        border-radius: 50% !important;
    }

    .avatar-cropper-modal .cropper-view-box {
        outline: 2px solid #22c55e !important;
        box-shadow: 0 0 0 9999px rgba(0, 0, 0, 0.55) !important;
    }

    .avatar-cropper-modal .cropper-face {
        background-color: transparent !important;
    }

    .avatar-cropper-modal .cropper-dashed,
    .avatar-cropper-modal .cropper-center,
    .avatar-cropper-modal .cropper-line,
    .avatar-cropper-modal .cropper-point {
        display: none !important;
    }
</style>

<div
    id="{{ $modalId }}"
    class="avatar-cropper-modal fixed inset-0 z-[60] hidden items-center justify-center bg-black/80 backdrop-blur-sm px-3 sm:px-4 py-3 sm:py-6"
    style="padding-top: max(env(safe-area-inset-top), 0.75rem); padding-bottom: max(env(safe-area-inset-bottom), 0.75rem);"
>
    <div class="bg-[#0f172a] rounded-2xl w-full max-w-3xl p-4 sm:p-6 shadow-2xl border border-white/10 h-[min(88dvh,720px)] sm:h-[min(92dvh,760px)] overflow-hidden flex flex-col">
        <h2 class="text-white text-xl mb-3 sm:mb-4 shrink-0">Ajusta tu foto</h2>

        <div class="w-full flex-1 min-h-0 h-[min(36dvh,300px)] sm:h-[360px] bg-slate-950/60 rounded-xl overflow-hidden flex items-center justify-center">
            <img id="{{ $cropImageId }}" alt="Editor de avatar" class="max-w-full">
        </div>

        <div class="shrink-0 mt-3 sm:mt-5 bg-[#0f172a] pt-2 pb-[max(env(safe-area-inset-bottom),0px)]">
        <div class="grid grid-cols-3 gap-2.5 sm:flex sm:flex-nowrap sm:justify-end sm:gap-4">
            <button id="{{ $modalId }}_btnReset" type="button" class="min-h-[44px] px-3 sm:px-4 py-2 bg-gray-600 rounded-lg text-white hover:bg-gray-500 transition">
                Reintentar
            </button>

            <button id="{{ $modalId }}_btnCancel" type="button" class="min-h-[44px] px-3 sm:px-4 py-2 bg-red-500 rounded-lg text-white hover:bg-red-400 transition">
                Cancelar
            </button>

            <button id="{{ $modalId }}_btnApply" type="button" class="min-h-[44px] px-3 sm:px-4 py-2 bg-green-500 rounded-lg text-white hover:bg-green-400 transition">
                Aplicar
            </button>
        </div>
        </div>
    </div>
</div>

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

        const resolveCropperConstructor = () => window.Cropper?.default || window.Cropper;

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

        const applyCoverScale = (extraMargin = 0.05) => {
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
                const CropperConstructor = resolveCropperConstructor();
                if (!CropperConstructor) {
                    console.error('CropperJS no está disponible en window.Cropper');
                    return;
                }

                cropper = new CropperConstructor(imageToEdit, {
                    aspectRatio: 1,
                    viewMode: 1,
                    autoCropArea: 1,
                    responsive: true,
                    background: false,
                    ready() {
                        applyCoverScale(0.05);
                    },
                });
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
            destroyCropper();
            cleanupObjectUrl();

            if (cropSource === 'persistent' && cropSourceInput) {
                cropSourceInput.click();
                return;
            }

            if (cropSource === 'dynamic') {
                const input = document.createElement('input');
                input.type = 'file';
                input.accept = 'image/*';
                input.onchange = (event) => {
                    const [file] = event.target.files || [];
                    loadFileIntoCropper(file);
                };
                input.click();
            }
        });

        applyBtn.addEventListener('click', () => {
            if (!cropper) return;

            const canvas = cropper.getCroppedCanvas({
                width: 300,
                height: 300,
                imageSmoothingQuality: 'high',
            });
            if (!canvas) return;

            const size = 300;
            const circleCanvas = document.createElement('canvas');
            circleCanvas.width = size;
            circleCanvas.height = size;
            const ctx = circleCanvas.getContext('2d');
            if (!ctx) return;

            ctx.clearRect(0, 0, size, size);
            ctx.drawImage(canvas, 0, 0, size, size);
            ctx.globalCompositeOperation = 'destination-in';
            ctx.beginPath();
            ctx.arc(size / 2, size / 2, size / 2, 0, Math.PI * 2);
            ctx.closePath();
            ctx.fill();
            ctx.globalCompositeOperation = 'source-over';

            const base64 = circleCanvas.toDataURL('image/png');
            base64Input.value = base64;
            preview.src = base64;
            preview.classList.remove('hidden');

            if (mode === 'profile') {
                closeImageModal();
                destroyCropper();
                cleanupObjectUrl();
                return;
            }

            circleCanvas.toBlob((blob) => {
                if (!blob || !dataTransferInput) return;

                const file = new File([blob], 'profile-cropped.png', { type: 'image/png' });
                const dt = new DataTransfer();
                dt.items.add(file);
                dataTransferInput.files = dt.files;

                closeImageModal();
                destroyCropper();
                cleanupObjectUrl();
            }, 'image/png');
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
