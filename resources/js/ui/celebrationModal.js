import { illustrationFromBundle } from './emptyState.js';

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
        <img src="${escapeHtml(src)}" alt="${alt}" width="320" height="240" loading="eager" decoding="async" draggable="false" class="h-auto w-full object-contain">
    </picture>`;
}

/**
 * @param {{
 *   illustrations?: Record<string, { url?: string, webp?: string|null, png?: string, alt?: string }>,
 * }} config
 */
export function showFirstPostCelebration(config) {
    const modal = document.getElementById('celebration-modal');
    const backdrop = document.getElementById('celebration-modal-backdrop');
    const artEl = document.getElementById('celebration-modal-art');
    const closeBtn = document.getElementById('celebration-modal-close');

    if (!modal || !artEl || !closeBtn) {
        return;
    }

    const ill = illustrationFromBundle(config.illustrations, 'community-celebration-milestone');
    artEl.innerHTML = renderArtHtml(ill);

    function close() {
        modal.classList.add('hidden');
        modal.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('overflow-hidden');
    }

    closeBtn.addEventListener('click', close, { once: true });
    backdrop?.addEventListener('click', close, { once: true });

    modal.classList.remove('hidden');
    modal.setAttribute('aria-hidden', 'false');
    document.body.classList.add('overflow-hidden');
}
