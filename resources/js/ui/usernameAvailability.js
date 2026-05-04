/**
 * Comprobación asíncrona de disponibilidad de username (perfil).
 */
export function initUsernameAvailability() {
    const input = document.getElementById('username');
    const status = document.getElementById('username-availability');
    const preview = document.getElementById('username-url-preview');
    if (!input || !status) {
        return;
    }

    const checkUrl = input.getAttribute('data-check-url');
    if (!checkUrl || !window.axios) {
        return;
    }

    const debounceMs = 500;
    const originalUsername = (input.dataset.original || '').trim().toLowerCase();
    let timer = null;
    let lastChecked = null;
    let abortController = null;

    const setStatus = (message, className) => {
        status.textContent = message;
        status.className = `mt-1 text-xs min-h-[1.25rem] ${className || 'text-gray-500'}`;
    };

    const showDefaultState = () => setStatus('', 'text-gray-500');
    const showLoading = () => setStatus('Comprobando...', 'text-gray-500');
    const showAvailable = () => setStatus('Disponible', 'text-green-400');
    const showTaken = () => setStatus('No disponible', 'text-red-400');
    const showError = () => setStatus('No se pudo comprobar. Reintenta.', 'text-amber-400/90');

    async function validateUsername(raw) {
        const normalized = raw.toLowerCase();

        if (normalized === originalUsername) {
            showDefaultState();
            lastChecked = null;

            return;
        }

        if (normalized === lastChecked) {
            return;
        }

        if (raw.length < 3) {
            setStatus('Mínimo 3 caracteres.', 'text-amber-400/90');

            return;
        }
        if (raw.length > 30) {
            setStatus('Máximo 30 caracteres.', 'text-red-400');

            return;
        }
        if (!/^[a-z0-9_-]+$/.test(raw)) {
            setStatus('Solo minúsculas, números, guiones y guion bajo.', 'text-amber-400/90');

            return;
        }

        if (abortController) {
            abortController.abort();
        }
        abortController = new AbortController();

        showLoading();
        try {
            const response = await window.axios.get(checkUrl, {
                params: { username: raw },
                signal: abortController.signal,
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });

            lastChecked = normalized;
            if (response.data?.available) {
                showAvailable();

                return;
            }

            showTaken();
        } catch (error) {
            if (error?.name === 'CanceledError' || error?.code === 'ERR_CANCELED') {
                return;
            }

            if (error?.response?.status === 422) {
                setStatus('Formato no válido.', 'text-red-400');

                return;
            }

            showError();
        }
    }

    function scheduleValidation() {
        const raw = (input.value || '').trim();
        if (preview) {
            preview.textContent = raw || originalUsername;
        }

        if (timer) {
            clearTimeout(timer);
        }
        timer = setTimeout(() => validateUsername(raw), debounceMs);
    }

    input.addEventListener('input', scheduleValidation);
}
