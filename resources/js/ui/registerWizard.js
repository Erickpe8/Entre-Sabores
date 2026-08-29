import { showAlert } from './alerts.js';
import { ensureRegisterCountryDetected } from './registerCountryLocation.js';

const ALLOWED_COUNTRIES = new Set([
    'Colombia',
    'México',
    'Argentina',
    'Chile',
    'Perú',
    'Ecuador',
    'Venezuela',
    'Bolivia',
    'Paraguay',
    'Uruguay',
]);

/**
 * Wizard de registro — independiente del avatar cropper.
 */
export function initRegisterWizard() {
    const form = document.getElementById('register-form');
    if (!form || !form.hasAttribute('data-auth-wizard')) {
        return;
    }

    const steps = [...form.querySelectorAll('.register-step')];
    if (steps.length === 0) {
        return;
    }

    const stepCurrentLabel = document.getElementById('register-step-current');
    const stepProgress = document.getElementById('register-progress');

    const firstNameField = document.getElementById('first_name');
    const lastNameField = document.getElementById('last_name');
    const emailField = document.getElementById('email');
    const passwordField = document.getElementById('password');
    const passwordConfirmationField = document.getElementById('password_confirmation');
    const countryField = document.getElementById('country');
    const descriptionField = document.getElementById('description');
    const photoField = document.getElementById('profile_photo');
    const openEditorBtn = document.getElementById('openEditor');

    let currentStep = 0;
    /** @type {Map<string, string>} */
    const fieldErrors = new Map();

    const setFieldError = (field, message = '') => {
        if (!field) {
            return;
        }

        field.setCustomValidity(message);
        const hasError = message !== '';
        field.classList.toggle('border-danger', hasError);
        field.classList.toggle('ring-2', hasError);
        field.classList.toggle('ring-danger/30', hasError);

        if (hasError) {
            fieldErrors.set(field.id, message);
            return;
        }

        fieldErrors.delete(field.id);
    };

    const clearFieldErrors = () => {
        fieldErrors.clear();
        [
            firstNameField,
            lastNameField,
            emailField,
            passwordField,
            passwordConfirmationField,
            countryField,
            descriptionField,
            photoField,
        ].forEach((field) => setFieldError(field));
    };

    const publishFieldErrors = () => {
        const messages = [...new Set(fieldErrors.values())];
        messages.forEach((message) => showAlert(message, { type: 'danger', client: true }));

        const firstFieldId = fieldErrors.keys().next().value;
        if (firstFieldId) {
            document.getElementById(firstFieldId)?.focus();
        }

        return messages.length > 0;
    };

    const getStepFields = (stepIndex) => {
        const step = steps[stepIndex];
        if (!step) {
            return [];
        }

        return (step.dataset.stepFields ?? '')
            .split(',')
            .map((id) => id.trim())
            .filter(Boolean)
            .map((id) => document.getElementById(id))
            .filter(Boolean);
    };

    const updateProgress = (stepIndex) => {
        if (stepCurrentLabel) {
            stepCurrentLabel.textContent = String(stepIndex + 1);
        }

        stepProgress?.querySelectorAll('[data-step-dot]').forEach((dot, index) => {
            dot.classList.toggle('is-active', index <= stepIndex);
            dot.classList.toggle('is-complete', index < stepIndex);
        });
    };

    const focusStepField = (stepIndex) => {
        const focusTarget = getStepFields(stepIndex).find(
            (field) => field?.type !== 'file' && field?.type !== 'hidden',
        );

        if (!focusTarget) {
            return;
        }

        requestAnimationFrame(() => {
            focusTarget.focus({ preventScroll: true });
        });
    };

    const showStep = (stepIndex) => {
        const safeIndex = Math.max(0, Math.min(stepIndex, steps.length - 1));

        steps.forEach((step, index) => {
            const isActive = index === safeIndex;
            step.classList.toggle('hidden', !isActive);
            step.toggleAttribute('hidden', !isActive);
        });

        currentStep = safeIndex;
        updateProgress(safeIndex);

        if (safeIndex === 1) {
            document.dispatchEvent(new CustomEvent('register:step-2'));
        }

        focusStepField(safeIndex);
    };

    const validateFieldGroup = (fields) => {
        const invalidField = fields.find(
            (field) => field && (!field.checkValidity() || fieldErrors.has(field.id)),
        );

        if (!invalidField) {
            return true;
        }

        if (!fieldErrors.has(invalidField.id)) {
            setFieldError(invalidField, invalidField.validationMessage || 'Revisa este campo.');
        }

        publishFieldErrors();
        return false;
    };

    const applyDescriptionRules = () => {
        setFieldError(descriptionField);

        const descriptionValue = descriptionField?.value ?? '';
        if (descriptionValue.length > 500) {
            setFieldError(descriptionField, 'La descripción no puede superar los 500 caracteres.');
        }
    };

    const applyPhotoRules = () => {
        setFieldError(photoField);

        const photo = photoField?.files?.[0] ?? null;
        if (!photo) {
            setFieldError(photoField, 'La foto de perfil es obligatoria.');
            return;
        }

        const allowedMimeTypes = new Set(['image/jpeg', 'image/png', 'image/webp']);
        if (!allowedMimeTypes.has(photo.type)) {
            setFieldError(photoField, 'La foto de perfil debe ser JPG, PNG o WebP.');
        }

        const maxBytes = 2 * 1024 * 1024;
        if (photo.size > maxBytes) {
            setFieldError(photoField, 'La foto de perfil no puede superar los 2 MB.');
        }
    };

    const normalizeEmailField = () => {
        if (!emailField?.value) {
            return;
        }

        emailField.value = emailField.value.trim().toLowerCase();
    };

    const applyStepRules = (stepIndex) => {
        clearFieldErrors();

        if (stepIndex === 0) {
            normalizeEmailField();

            if (!firstNameField?.value?.trim()) {
                setFieldError(firstNameField, 'Escribe tu nombre.');
            } else if (firstNameField.value.trim().length > 50) {
                setFieldError(firstNameField, 'El nombre no puede superar los 50 caracteres.');
            }

            if (!lastNameField?.value?.trim()) {
                setFieldError(lastNameField, 'Escribe tu apellido.');
            } else if (lastNameField.value.trim().length > 50) {
                setFieldError(lastNameField, 'El apellido no puede superar los 50 caracteres.');
            }

            if (!emailField?.value?.trim()) {
                setFieldError(emailField, 'Necesitamos tu correo electrónico.');
            } else if (emailField.value.length > 255) {
                setFieldError(emailField, 'El correo no puede superar 255 caracteres.');
            } else if (!emailField.checkValidity()) {
                setFieldError(emailField, 'Ese correo no es válido.');
            }

            return;
        }

        if (stepIndex === 1) {
            applyDescriptionRules();

            if (countryField && !ALLOWED_COUNTRIES.has(countryField.value)) {
                setFieldError(countryField, 'Selecciona un país válido de la lista.');
            }

            return;
        }

        if (stepIndex === 2) {
            if (!passwordField?.value) {
                setFieldError(passwordField, 'Crea una contraseña.');
            } else if (passwordField.value.length < 8) {
                setFieldError(passwordField, 'Tu contraseña debe tener al menos 8 caracteres.');
            }

            if (
                passwordConfirmationField &&
                passwordField &&
                passwordConfirmationField.value !== passwordField.value
            ) {
                setFieldError(passwordConfirmationField, 'Las contraseñas no coinciden.');
            }

            return;
        }

        if (stepIndex === 3) {
            applyPhotoRules();
        }
    };

    const validateStep = (stepIndex) => validateFieldGroup(getStepFields(stepIndex));

    const validateAllSteps = async () => {
        if (!countryField?.value) {
            await ensureRegisterCountryDetected();
        }

        for (let index = 0; index < steps.length; index += 1) {
            applyStepRules(index);
            if (!validateStep(index)) {
                showStep(index);
                return false;
            }
        }

        return true;
    };

    const initialStep = Number.parseInt(form.dataset.registerInitialStep ?? '0', 10);
    showStep(Number.isFinite(initialStep) ? initialStep : 0);

    form.querySelectorAll('[data-register-next]').forEach((button) => {
        button.addEventListener('click', async () => {
            if (currentStep === 1 && !countryField?.value) {
                await ensureRegisterCountryDetected();
            }

            applyStepRules(currentStep);
            if (!validateStep(currentStep)) {
                return;
            }

            showStep(currentStep + 1);
        });
    });

    form.querySelectorAll('[data-register-back]').forEach((button) => {
        button.addEventListener('click', () => {
            showStep(currentStep - 1);
        });
    });

    form.addEventListener('submit', async (event) => {
        if (!(await validateAllSteps())) {
            event.preventDefault();
            openEditorBtn?.focus();
        }
    });

    [
        firstNameField,
        lastNameField,
        emailField,
        passwordField,
        passwordConfirmationField,
        countryField,
        descriptionField,
        photoField,
    ].forEach((field) => {
        if (!field) {
            return;
        }

        field.addEventListener('input', () => setFieldError(field));
        field.addEventListener('change', () => setFieldError(field));
    });
}
