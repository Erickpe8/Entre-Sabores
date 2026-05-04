/**
 * Menú móvil fullscreen (navbar). CSP-safe (sin eval).
 */
export function initMobileNav() {
    const nav = document.querySelector('[data-mobile-nav]');
    const toggle = document.getElementById('mobile-nav-toggle');
    const layer = document.getElementById('mobile-nav-layer');
    const backdrop = document.getElementById('mobile-nav-backdrop');
    const drawer = document.getElementById('mobile-nav-drawer');
    const closeBtn = document.getElementById('mobile-nav-close');
    const iconMenu = document.getElementById('mobile-nav-icon-menu');
    const iconClose = document.getElementById('mobile-nav-icon-x');

    if (!nav || !toggle || !layer || !backdrop || !drawer) {
        return;
    }

    let open = false;

    function syncIcons() {
        iconMenu?.classList.toggle('hidden', open);
        iconClose?.classList.toggle('hidden', !open);
        toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
    }

    function afterLeave(fn) {
        const dur = 280;
        window.setTimeout(fn, dur);
    }

    function setOpen(next) {
        if (next === open) {
            return;
        }
        open = next;
        syncIcons();
        document.body.classList.toggle('overflow-hidden', open);

        if (open) {
            layer.classList.remove('hidden');
            layer.setAttribute('aria-hidden', 'false');
            requestAnimationFrame(() => {
                backdrop.classList.remove('opacity-0');
                backdrop.classList.add('opacity-100');
                drawer.classList.remove('translate-x-full');
                drawer.classList.add('translate-x-0');
            });
            return;
        }

        layer.setAttribute('aria-hidden', 'true');
        backdrop.classList.add('opacity-0');
        backdrop.classList.remove('opacity-100');
        drawer.classList.add('translate-x-full');
        drawer.classList.remove('translate-x-0');
        afterLeave(() => {
            if (!open) {
                layer.classList.add('hidden');
            }
        });
    }

    toggle.addEventListener('click', () => setOpen(!open));
    closeBtn?.addEventListener('click', () => setOpen(false));
    backdrop.addEventListener('click', () => setOpen(false));

    window.addEventListener('close-mobile-menu', () => setOpen(false), false);

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && open) {
            setOpen(false);
        }
    });
}
