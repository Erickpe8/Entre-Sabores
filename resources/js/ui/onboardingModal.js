import { illustrationFromBundle } from './emptyState.js';

const STORAGE_KEY = 'es:onboarding:v1';

/**
 * @param {string} value
 */
function escapeHtml(value) {
    return String(value)
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#39;');
}

/**
 * @param {{ url?: string|null, webp?: string|null, png?: string|null, alt?: string }|null} ill
 */
function renderArtHtml(ill) {
    if (!ill?.url && !ill?.png && !ill?.webp) {
        return '';
    }

    const src = ill.url ?? ill.png ?? ill.webp ?? '';
    const alt = escapeHtml(ill.alt ?? '');

    return `<picture class="block w-full">
        ${ill.webp ? `<source srcset="${escapeHtml(ill.webp)}" type="image/webp">` : ''}
        <img src="${escapeHtml(src)}" alt="${alt}" width="480" height="270" loading="eager" decoding="async" draggable="false" class="h-auto w-full rounded-xl object-contain">
    </picture>`;
}

/** @type {readonly { key: string, title: string, message: string }[]} */
const STEPS = [
    {
        key: 'onboarding-discover-flavors',
        title: 'Descubre sabores del mundo',
        message: 'Explora maridajes y experiencias de toda la comunidad en tu muro personalizado.',
    },
    {
        key: 'onboarding-share-pairing',
        title: 'Comparte tu maridaje',
        message: 'Publica la combinación de comida y bebida que quieras contar, con foto y etiquetas.',
    },
    {
        key: 'onboarding-explore-cultures',
        title: 'Explora culturas y etiquetas',
        message: 'Filtra por país, tipo de comida o bebida y encuentra voces que te inspiren.',
    },
];

/**
 * @param {{ showOnboarding?: boolean, illustrations?: Record<string, { url?: string, webp?: string|null, png?: string, alt?: string }> }} config
 */
export function initOnboardingModal(config) {
    if (config.showOnboarding !== true) {
        return;
    }

    if (localStorage.getItem(STORAGE_KEY) === '1') {
        return;
    }

    const modal = document.getElementById('onboarding-modal');
    const backdrop = document.getElementById('onboarding-modal-backdrop');
    const artEl = document.getElementById('onboarding-modal-art');
    const titleEl = document.getElementById('onboarding-modal-title');
    const messageEl = document.getElementById('onboarding-modal-message');
    const dotsEl = document.getElementById('onboarding-modal-dots');
    const prevBtn = document.getElementById('onboarding-modal-prev');
    const nextBtn = document.getElementById('onboarding-modal-next');
    const skipBtn = document.getElementById('onboarding-modal-skip');

    if (!modal || !artEl || !titleEl || !messageEl || !nextBtn || !skipBtn) {
        return;
    }

    let stepIndex = 0;

    function finish() {
        localStorage.setItem(STORAGE_KEY, '1');
        modal.classList.add('hidden');
        modal.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('overflow-hidden');
    }

    function renderDots() {
        if (!dotsEl) {
            return;
        }

        dotsEl.innerHTML = STEPS.map((_, index) => {
            const active = index === stepIndex;

            return `<span class="onboarding-modal__dot ${active ? 'onboarding-modal__dot--active' : ''}"></span>`;
        }).join('');
    }

    function renderStep() {
        const step = STEPS[stepIndex];
        if (!step) {
            finish();

            return;
        }

        const ill = illustrationFromBundle(config.illustrations, step.key);
        artEl.innerHTML = renderArtHtml(ill);
        titleEl.textContent = step.title;
        messageEl.textContent = step.message;

        prevBtn?.classList.toggle('hidden', stepIndex === 0);
        nextBtn.textContent = stepIndex === STEPS.length - 1 ? 'Empezar' : 'Siguiente';

        renderDots();
    }

    function open() {
        modal.classList.remove('hidden');
        modal.setAttribute('aria-hidden', 'false');
        document.body.classList.add('overflow-hidden');
        stepIndex = 0;
        renderStep();
    }

    nextBtn.addEventListener('click', () => {
        if (stepIndex >= STEPS.length - 1) {
            finish();

            return;
        }

        stepIndex += 1;
        renderStep();
    });

    prevBtn?.addEventListener('click', () => {
        if (stepIndex <= 0) {
            return;
        }

        stepIndex -= 1;
        renderStep();
    });

    skipBtn.addEventListener('click', finish);
    backdrop?.addEventListener('click', finish);

    window.setTimeout(open, 400);
}
