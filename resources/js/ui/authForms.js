import { clearClientAlerts, showAlert } from './alerts.js';

const EMAIL_PATTERN = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

function isFieldVisible(field) {
    if (!(field instanceof HTMLElement) || field.disabled || field.type === 'hidden') {
        return false;
    }

    return field.closest('.hidden') === null && field.closest('[hidden]') === null;
}

function validateEmail(value) {
    const trimmed = String(value ?? '').trim();
    if (trimmed === '') {
        return 'Por favor, ingresa tu correo electrónico.';
    }
    if (!EMAIL_PATTERN.test(trimmed)) {
        return 'El correo electrónico no es válido.';
    }

    return null;
}

function validatePassword(value, minLength = 6) {
    const password = String(value ?? '');
    if (password === '') {
        return 'Ingresa tu contraseña.';
    }
    if (password.length < minLength) {
        return `La contraseña debe tener al menos ${minLength} caracteres.`;
    }

    return null;
}

function fieldLabel(form, field) {
    const label = form.querySelector(`label[for="${field.id}"]`)?.textContent?.trim();
    return label && label !== '' ? label : 'Este campo';
}

function collectAuthFormErrors(form) {
    const errors = [];
    const seen = new Set();

    const push = (message) => {
        const text = String(message ?? '').trim();
        if (text === '' || seen.has(text)) {
            return;
        }
        seen.add(text);
        errors.push(text);
    };

    const passwordField = form.querySelector('[name="password"]');
    const hasConfirmation = form.querySelector('[name="password_confirmation"]') !== null;
    const passwordMin = hasConfirmation ? 8 : 6;

    form.querySelectorAll('input, select, textarea').forEach((field) => {
        if (!isFieldVisible(field)) {
            return;
        }

        const { name, value } = field;

        if (name === 'email') {
            push(validateEmail(value));
            return;
        }

        if (name === 'password') {
            push(validatePassword(value, passwordMin));
            return;
        }

        if (name === 'password_confirmation') {
            if (String(value ?? '') === '') {
                push('Confirma tu contraseña.');
                return;
            }
            if (passwordField && value !== passwordField.value) {
                push('Las contraseñas no coinciden.');
            }
            return;
        }

        if (field.required && String(value ?? '').trim() === '') {
            push(`${fieldLabel(form, field)} es obligatorio.`);
        }
    });

    return errors;
}

function showAuthAlerts(messages, type = 'danger') {
    clearClientAlerts();
    messages.forEach((message) => {
        showAlert(message, { type, client: true });
    });
}

function bindAuthForm(form) {
    if (form.dataset.authBound === '1') {
        return;
    }

    form.dataset.authBound = '1';
    form.setAttribute('novalidate', 'novalidate');

    form.addEventListener(
        'invalid',
        (event) => {
            event.preventDefault();
        },
        true,
    );

    form.addEventListener('submit', (event) => {
        const errors = collectAuthFormErrors(form);
        if (errors.length > 0) {
            event.preventDefault();
            showAuthAlerts(errors);
        }
    });
}

export function initAuthForms() {
    document.querySelectorAll('form.auth-form:not([data-auth-wizard])').forEach(bindAuthForm);
}
