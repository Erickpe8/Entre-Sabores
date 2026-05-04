/**
 * Avatar cropper (Cropper.js) — configuración leída desde <textarea data-avatar-cropper-config>.
 */
export function initAvatarCroppers() {
    document.querySelectorAll('textarea[data-avatar-cropper-config]').forEach((el) => {
        const raw = el.value || el.textContent || '';
        if (!raw.trim()) {
            return;
        }
        let config;
        try {
            config = JSON.parse(raw);
        } catch {
            return;
        }
        initAvatarCropperInstance(config);
    });
}

/**
 * @param {Record<string, unknown>} config
 */
function initAvatarCropperInstance(config) {
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

    const openBtn = document.getElementById(String(openButtonId));
    const base64Input = document.getElementById(String(base64InputId));
    const preview = document.getElementById(String(previewId));
    const imageToEdit = document.getElementById(String(cropImageId));
    const modal = document.getElementById(String(modalId));
    const cancelBtn = document.getElementById(`${modalId}_btnCancel`);
    const resetBtn = document.getElementById(`${modalId}_btnReset`);
    const applyBtn = document.getElementById(`${modalId}_btnApply`);

    const cropSourceInput = cropSourceInputId ? document.getElementById(String(cropSourceInputId)) : null;
    const dataTransferInput = dataTransferInputId ? document.getElementById(String(dataTransferInputId)) : null;
    const form = formId ? document.getElementById(String(formId)) : null;
    const step1 = stepOneId ? document.getElementById(String(stepOneId)) : null;
    const step2 = stepTwoId ? document.getElementById(String(stepTwoId)) : null;

    const step1Fields =
        mode === 'register' && step1
            ? ['first_name', 'last_name', 'email', 'password', 'password_confirmation', 'country'].map((id) =>
                  document.getElementById(id),
              )
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

    const getCoverScaleFromImage = () => {
        if (!cropper) {
            return null;
        }
        const containerData = cropper.getContainerData();
        const imageData = cropper.getImageData();
        if (!containerData?.width || !containerData?.height || !imageData?.naturalWidth || !imageData?.naturalHeight) {
            return null;
        }

        return Math.max(containerData.width / imageData.naturalWidth, containerData.height / imageData.naturalHeight);
    };

    const applyCoverScale = (extraMargin = 0.05) => {
        const coverScale = getCoverScaleFromImage();
        if (!coverScale) {
            return null;
        }
        const targetScale = coverScale + extraMargin;
        cropper.zoomTo(targetScale);

        return targetScale;
    };

    const loadFileIntoCropper = (file) => {
        if (!file) {
            return;
        }

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
            if (!isValid) {
                return;
            }

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
        if (!cropper) {
            return;
        }

        const canvas = cropper.getCroppedCanvas({
            width: 300,
            height: 300,
            imageSmoothingQuality: 'high',
        });
        if (!canvas) {
            return;
        }

        const size = 300;
        const circleCanvas = document.createElement('canvas');
        circleCanvas.width = size;
        circleCanvas.height = size;
        const ctx = circleCanvas.getContext('2d');
        if (!ctx) {
            return;
        }

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
            if (!blob || !dataTransferInput) {
                return;
            }

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
}
