/**
 * Mensajes flash que se ocultan solos (reemplazo de x-show + x-init timeout).
 */
export function initFlashAutoHide() {
    document.querySelectorAll('[data-flash-auto-hide]').forEach((el) => {
        const raw = el.dataset.flashAutoHide;
        const ms = Number.parseInt(raw ?? '2500', 10);
        const delay = Number.isFinite(ms) && ms > 0 ? ms : 2500;

        window.setTimeout(() => {
            el.classList.add('opacity-0');
            window.setTimeout(() => {
                el.classList.add('hidden');
            }, 280);
        }, delay);
    });
}
