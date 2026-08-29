/**
 * Dropdowns tipo cuenta (componente dropdown Blade). CSP-safe.
 */
export function initDropdowns() {
    document.querySelectorAll('[data-dropdown]').forEach((root) => {
        const trigger = root.querySelector('[data-dropdown-trigger]');
        const panel = root.querySelector('[data-dropdown-panel]');

        if (!trigger || !panel) {
            return;
        }

        let open = false;

        const ariaEl = trigger.matches('button') ? trigger : trigger.querySelector('button');

        (ariaEl ?? trigger).setAttribute('aria-expanded', 'false');

        function render() {
            const ariaTarget = trigger.matches('button') ? trigger : trigger.querySelector('button');
            (ariaTarget ?? trigger).setAttribute('aria-expanded', open ? 'true' : 'false');
            if (open) {
                panel.classList.remove('hidden');
                requestAnimationFrame(() => {
                    panel.classList.remove('opacity-0', 'scale-95');
                    panel.classList.add('opacity-100', 'scale-100');
                });
                return;
            }

            panel.classList.remove('opacity-100', 'scale-100');
            panel.classList.add('opacity-0', 'scale-95');
            window.setTimeout(() => {
                if (!open) {
                    panel.classList.add('hidden');
                }
            }, 75);
        }

        trigger.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            open = !open;
            render();
        });

        panel.querySelectorAll('a').forEach((a) => {
            a.addEventListener('click', () => {
                open = false;
                render();
            });
        });

        document.addEventListener(
            'click',
            (e) => {
                if (!open || root.contains(e.target)) {
                    return;
                }
                open = false;
                render();
            },
            true,
        );

        root.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && open) {
                open = false;
                render();
            }
        });
    });
}
