import { showAlert } from './alerts.js';

const TIMEZONE_COUNTRY = {
    'America/Bogota': 'Colombia',
    'America/Mexico_City': 'México',
    'America/Cancun': 'México',
    'America/Mazatlan': 'México',
    'America/Tijuana': 'México',
    'America/Argentina/Buenos_Aires': 'Argentina',
    'America/Santiago': 'Chile',
    'America/Lima': 'Perú',
    'America/Guayaquil': 'Ecuador',
    'America/Caracas': 'Venezuela',
    'America/La_Paz': 'Bolivia',
    'America/Asuncion': 'Paraguay',
    'America/Montevideo': 'Uruguay',
};

/** @type {Promise<void> | null} */
let detectionPromise = null;

function countryFromTimezone() {
    const timezone = Intl.DateTimeFormat().resolvedOptions().timeZone;
    const country = TIMEZONE_COUNTRY[timezone];

    return country ? { country, source: 'timezone' } : null;
}

function applyCountry(select, country) {
    if (!select || !country) {
        return false;
    }

    const option = [...select.options].find((entry) => entry.value === country);
    if (!option) {
        return false;
    }

    select.value = country;
    select.setCustomValidity('');

    return true;
}

function formatDetectionMessage(country, source) {
    if (source === 'ip') {
        return `Según tu conexión, parece que estás en ${country}. Puedes cambiarlo si no es correcto.`;
    }

    if (source === 'timezone') {
        return `Según la zona horaria de tu dispositivo, probablemente estás en ${country}. Puedes cambiarlo si no es correcto.`;
    }

    return `Detectamos que estás en ${country}. Puedes cambiarlo si no es correcto.`;
}

async function fetchCountryHint(url) {
    const response = await fetch(url, {
        headers: {
            Accept: 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
        },
    });

    if (!response.ok) {
        return null;
    }

    const payload = await response.json();
    if (!payload?.detected || !payload?.country) {
        return null;
    }

    return {
        country: payload.country,
        source: payload.source ?? 'ip',
    };
}

/**
 * Espera a que termine la detección automática del país (si está en curso).
 */
export function ensureRegisterCountryDetected() {
    return detectionPromise ?? Promise.resolve();
}

/**
 * Preselecciona el país y avisa con alerta informativa al entrar al paso 2.
 */
export function initRegisterCountryLocation() {
    const step = document.getElementById('step-2');
    const countrySelect = document.getElementById('country');

    if (!step || !countrySelect) {
        return;
    }

    const hintUrl = step.dataset.countryHintUrl;
    let userChangedCountry = Boolean(countrySelect.value);
    let hasDetected = false;
    let alertShown = false;
    /** @type {{ country: string, source: string } | null} */
    let lastDetection = null;

    countrySelect.addEventListener('change', () => {
        userChangedCountry = true;
    });

    const showDetectionAlert = () => {
        if (alertShown || userChangedCountry || !lastDetection) {
            return;
        }

        if (countrySelect.value !== lastDetection.country) {
            return;
        }

        showAlert(formatDetectionMessage(lastDetection.country, lastDetection.source), {
            type: 'info',
            client: true,
        });
        alertShown = true;
    };

    const tryApply = (result) => {
        if (userChangedCountry || hasDetected || !result?.country) {
            return false;
        }

        if (!applyCountry(countrySelect, result.country)) {
            return false;
        }

        hasDetected = true;
        lastDetection = result;

        return true;
    };

    const runDetect = async () => {
        if (userChangedCountry || countrySelect.value) {
            return;
        }

        tryApply(countryFromTimezone());

        if (hasDetected || !hintUrl) {
            return;
        }

        try {
            const ipResult = await fetchCountryHint(hintUrl);
            tryApply(ipResult);
        } catch {
            // El usuario puede elegir el país manualmente.
        }
    };

    detectionPromise = runDetect();

    document.addEventListener('register:step-2', async () => {
        await detectionPromise;
        showDetectionAlert();
    });

    if (!step.hasAttribute('hidden')) {
        detectionPromise.then(showDetectionAlert);
    }
}
