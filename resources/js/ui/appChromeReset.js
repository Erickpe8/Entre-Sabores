/**
 * Limpia estado global del “chrome” (body scroll, menú móvil, notificaciones)
 * que a veces deja restos al venir del muro/modales o con bfcache.
 */
export function resetAppChromeState() {
    document.body.classList.remove('overflow-hidden', 'overflow-y-hidden');
    document.documentElement.classList.remove('overflow-hidden', 'overflow-y-hidden');

    window.dispatchEvent(new CustomEvent('close-mobile-menu'));
    window.dispatchEvent(new CustomEvent('entre-sabores:close-notifications'));
}

export function initAppChromePageshow() {
    window.addEventListener('pageshow', (ev) => {
        if (ev.persisted) {
            resetAppChromeState();
        }
    });
}
