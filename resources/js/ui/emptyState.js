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
 * @param {{
 *   url?: string|null,
 *   webp?: string|null,
 *   png?: string|null,
 *   alt?: string,
 *   title?: string,
 *   message?: string,
 *   className?: string,
 * }} opts
 */
export function renderEmptyStateHtml(opts) {
    const className = opts.className ? ` ui-empty-state ${opts.className}` : ' ui-empty-state';
    const alt = escapeHtml(opts.alt ?? '');
    const title = opts.title ? escapeHtml(opts.title) : '';
    const message = opts.message ? escapeHtml(opts.message) : '';
    const src = opts.url ?? opts.png ?? opts.webp ?? '';

    const art =
        src !== ''
            ? `<picture class="ui-empty-state__art mx-auto block w-full max-w-[12rem]">
                ${opts.webp ? `<source srcset="${escapeHtml(opts.webp)}" type="image/webp">` : ''}
                <img src="${escapeHtml(src)}" alt="${alt}" width="192" height="192" loading="lazy" decoding="async" draggable="false" class="h-auto w-full object-contain">
            </picture>`
            : '';

    return `<div class="${className.trim()}">
        ${art}
        ${title !== '' ? `<h3 class="ui-empty-state__title">${title}</h3>` : ''}
        ${message !== '' ? `<p class="ui-empty-state__message">${message}</p>` : ''}
    </div>`;
}

/**
 * @param {Record<string, { url?: string, webp?: string|null, png?: string, alt?: string }>|undefined|null} bundle
 * @param {string} name
 */
export function illustrationFromBundle(bundle, name) {
    const entry = bundle?.[name];
    if (!entry) {
        return null;
    }

    return {
        url: entry.url ?? entry.png ?? entry.webp ?? null,
        webp: entry.webp ?? null,
        png: entry.png ?? entry.url ?? null,
        alt: entry.alt ?? '',
    };
}
