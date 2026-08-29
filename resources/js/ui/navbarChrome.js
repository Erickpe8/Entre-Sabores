/**
 * Navbar sticky: sombra al scroll, atajo "/" para búsqueda del muro.
 */
export function initNavbarChrome() {
    const navbar = document.getElementById('app-navbar');
    if (!navbar) {
        return;
    }

    const syncScrollShadow = () => {
        navbar.classList.toggle('app-navbar--scrolled', window.scrollY > 4);
    };

    window.addEventListener('scroll', syncScrollShadow, { passive: true });
    syncScrollShadow();

    document.addEventListener('keydown', (e) => {
        if (e.key !== '/' || e.ctrlKey || e.metaKey || e.altKey) {
            return;
        }

        const target = e.target;
        if (
            target instanceof HTMLElement &&
            (target.isContentEditable ||
                target.closest('input, textarea, select, [contenteditable="true"]'))
        ) {
            return;
        }

        const search = document.querySelector('[data-wall-search]:not(.hidden)');
        if (!search) {
            const desktopSearch = document.getElementById('wall-search-q');
            const mobileSearch = document.getElementById('wall-search-q-mobile');
            const pick =
                desktopSearch && !desktopSearch.closest('.hidden') ? desktopSearch : mobileSearch;
            if (!pick) {
                return;
            }
            e.preventDefault();
            pick.focus();
            pick.select?.();

            return;
        }

        e.preventDefault();
        search.focus();
        search.select?.();
    });
}
