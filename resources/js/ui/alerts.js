const ALERT_VARIANTS = {
    info: 'alert-info',
    danger: 'alert-danger',
    success: 'alert-success',
    warning: 'alert-warning',
    dark: 'alert-info',
};

/** Tiempo visible antes de ocultarse automáticamente (ms). */
export const ALERT_AUTO_DISMISS_MS = 4500;

function escapeHtml(value) {
    return String(value)
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#39;');
}

function dismissAlert(el) {
    if (!el || el.dataset.dismissing === '1') {
        return;
    }

    el.dataset.dismissing = '1';
    el.classList.add('opacity-0', 'translate-x-2');
    window.setTimeout(() => el.remove(), 300);
}

function bindAlert(el, durationMs = ALERT_AUTO_DISMISS_MS) {
    if (!el || el.dataset.alertBound === '1') {
        return;
    }

    el.dataset.alertBound = '1';

    const dismissBtn = el.querySelector('.alert-dismiss');
    dismissBtn?.addEventListener('click', () => dismissAlert(el));

    if (durationMs > 0) {
        window.setTimeout(() => dismissAlert(el), durationMs);
    }
}

/**
 * @param {string} message
 * @param {{ type?: string, title?: string|null, duration?: number, client?: boolean }} [options]
 */
export function showAlert(message, { type = 'danger', title = null, duration = ALERT_AUTO_DISMISS_MS, client = false } = {}) {
    const stack = document.getElementById('alert-stack');
    if (!stack || !message?.trim()) {
        return null;
    }

    const variant = ALERT_VARIANTS[type] ?? ALERT_VARIANTS.danger;
    const el = document.createElement('div');
    el.className = `pointer-events-auto flex items-start gap-3 rounded-base border p-4 text-sm font-medium shadow-lg opacity-0 translate-x-2 transition-all duration-300 ${variant}`;
    el.setAttribute('role', 'alert');
    el.dataset.alertAutoDismiss = String(duration);
    if (client) {
        el.dataset.clientAlert = '1';
    }

    const body = document.createElement('div');
    body.className = 'min-w-0 flex-1';

    if (title) {
        body.innerHTML = `<span class="font-medium">${escapeHtml(title)}</span> <span>${escapeHtml(message)}</span>`;
    } else {
        body.textContent = message;
    }

    const dismissBtn = document.createElement('button');
    dismissBtn.type = 'button';
    dismissBtn.className =
        'alert-dismiss -m-1 shrink-0 rounded-base p-1 opacity-70 transition hover:opacity-100 focus:outline-none focus:ring-2 focus:ring-current/30';
    dismissBtn.setAttribute('aria-label', 'Cerrar alerta');
    dismissBtn.innerHTML =
        '<svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>';

    el.append(body, dismissBtn);
    stack.appendChild(el);

    requestAnimationFrame(() => {
        el.classList.remove('opacity-0', 'translate-x-2');
    });

    bindAlert(el, duration);

    return el;
}

export function clearClientAlerts() {
    document.querySelectorAll('#alert-stack [role="alert"][data-client-alert="1"]').forEach((el) => {
        dismissAlert(el);
    });
}

function bindExistingAlerts() {
    document.querySelectorAll('#alert-stack [role="alert"]:not([data-alert-bound="1"])').forEach((el) => {
        const raw = el.dataset.alertAutoDismiss;
        const parsed = Number.parseInt(raw ?? '', 10);
        const duration = Number.isFinite(parsed) && parsed > 0 ? parsed : ALERT_AUTO_DISMISS_MS;
        const isStatic = el.dataset.alertStatic === '1';

        if (!isStatic) {
            requestAnimationFrame(() => {
                el.classList.remove('opacity-0', 'translate-x-2');
            });
        }

        bindAlert(el, duration);
    });
}

export function initAlerts() {
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', bindExistingAlerts, { once: true });
    } else {
        bindExistingAlerts();
    }

    window.addEventListener('pageshow', bindExistingAlerts);
}
