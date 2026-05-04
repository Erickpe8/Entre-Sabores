/**
 * Modales globales (eventos open-modal / close-modal). CSP-safe.
 */

function focusablesIn(container) {
    const sel =
        'a[href], button:not([disabled]), input:not([disabled]):not([type="hidden"]), textarea:not([disabled]), select:not([disabled]), [tabindex]:not([tabindex="-1"])';

    return [...container.querySelectorAll(sel)].filter((el) => !el.hasAttribute('disabled'));
}

/** @type {Map<string, (open: boolean) => void>} */
const modalSetters = new Map();

let globalModalUiBound = false;

export function initModals() {
    document.querySelectorAll('[data-modal-root]').forEach((root) => {
        const name = root.dataset.modalRoot;
        const backdrop = root.querySelector('[data-modal-backdrop]');
        const panel = root.querySelector('[data-modal-panel]');
        const focusable = root.dataset.modalFocusable === '1';

        if (!name || !backdrop || !panel) {
            return;
        }

        let isOpen = root.dataset.modalInitialOpen === '1';

        function setOpen(next) {
            if (next === isOpen) {
                return;
            }
            isOpen = next;

            if (isOpen) {
                root.classList.remove('hidden');
                root.setAttribute('aria-hidden', 'false');
                document.body.classList.add('overflow-y-hidden');
                requestAnimationFrame(() => {
                    backdrop.classList.remove('opacity-0');
                    backdrop.classList.add('opacity-75');
                    panel.classList.remove('opacity-0', 'translate-y-4', 'sm:translate-y-0', 'sm:scale-95');
                    panel.classList.add('opacity-100', 'translate-y-0', 'sm:scale-100');
                });
                if (focusable) {
                    window.setTimeout(() => {
                        focusablesIn(panel)[0]?.focus();
                    }, 120);
                }

                return;
            }

            root.setAttribute('aria-hidden', 'true');
            document.body.classList.remove('overflow-y-hidden');
            backdrop.classList.add('opacity-0');
            backdrop.classList.remove('opacity-75');
            panel.classList.add('opacity-0', 'translate-y-4', 'sm:translate-y-0', 'sm:scale-95');
            panel.classList.remove('opacity-100', 'translate-y-0', 'sm:scale-100');
            window.setTimeout(() => {
                if (!isOpen) {
                    root.classList.add('hidden');
                }
            }, 200);
        }

        modalSetters.set(name, setOpen);

        if (isOpen) {
            root.classList.remove('hidden');
            root.setAttribute('aria-hidden', 'false');
            document.body.classList.add('overflow-y-hidden');
            backdrop.classList.remove('opacity-0');
            backdrop.classList.add('opacity-75');
            panel.classList.remove('opacity-0', 'translate-y-4', 'sm:translate-y-0', 'sm:scale-95');
            panel.classList.add('opacity-100', 'translate-y-0', 'sm:scale-100');
        }

        backdrop.addEventListener('click', () => setOpen(false));

        root.addEventListener('keydown', (e) => {
            if (!isOpen || e.key !== 'Tab' || !focusable) {
                return;
            }
            const list = focusablesIn(panel);
            if (list.length === 0) {
                return;
            }
            const first = list[0];
            const last = list[list.length - 1];
            if (e.shiftKey && document.activeElement === first) {
                e.preventDefault();
                last.focus();
            } else if (!e.shiftKey && document.activeElement === last) {
                e.preventDefault();
                first.focus();
            }
        });
    });

    if (!globalModalUiBound) {
        globalModalUiBound = true;

        window.addEventListener('open-modal', (e) => {
            modalSetters.get(e.detail)?.(true);
        });

        window.addEventListener('close-modal', (e) => {
            modalSetters.get(e.detail)?.(false);
        });

        document.addEventListener('click', (ev) => {
            const opener = ev.target.closest('[data-open-modal]');
            if (!opener) {
                return;
            }
            ev.preventDefault();
            const modalName = opener.dataset.openModal;
            if (modalName) {
                modalSetters.get(modalName)?.(true);
            }
        });

        document.addEventListener('click', (ev) => {
            const closer = ev.target.closest('[data-close-modal]');
            if (!closer) {
                return;
            }
            ev.preventDefault();
            const modalName = closer.dataset.closeModal;
            if (modalName) {
                modalSetters.get(modalName)?.(false);
            }
        });

        document.addEventListener('keydown', (e) => {
            if (e.key !== 'Escape') {
                return;
            }
            modalSetters.forEach((fn) => fn(false));
        });
    }
}
