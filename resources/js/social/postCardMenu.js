import { sharePost } from './postCardShare.js';

const MENU_PANEL =
    'post-card-menu-panel absolute right-0 top-full z-30 mt-1 hidden min-w-[11.5rem] overflow-hidden rounded-base border border-default bg-neutral-secondary-medium py-1 shadow-xs origin-top-right';

const MENU_ITEM =
    'flex w-full items-center gap-2 px-3 py-2 text-left text-sm text-body transition-colors hover:bg-neutral-tertiary-medium hover:text-heading focus:outline-none focus-visible:bg-neutral-tertiary-medium focus-visible:text-heading';

/**
 * @param {object} post
 */
export function buildPostMenuHtml(post) {
    const canEdit = post.can_edit === true;
    const editItem = canEdit
        ? `<button type="button" class="${MENU_ITEM} wall-post-menu-action" role="menuitem" data-post-menu-action="edit" data-post-id="${post.id}">Editar publicación</button>`
        : '';

    return `
        <div class="relative shrink-0" data-post-menu data-post-id="${post.id}">
            <button
                type="button"
                class="wall-post-menu-btn inline-flex min-h-8 min-w-8 items-center justify-center rounded-base text-body hover:text-accent-warm focus:outline-none focus:ring-4 focus:ring-neutral-tertiary"
                data-post-menu-trigger
                aria-label="Opciones de la publicación"
                aria-haspopup="menu"
                aria-expanded="false"
            >
                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true"><path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z"/></svg>
            </button>
            <div class="${MENU_PANEL}" role="menu" aria-label="Opciones de la publicación">
                <button type="button" class="${MENU_ITEM} wall-post-menu-action" role="menuitem" data-post-menu-action="open" data-post-id="${post.id}">Ver publicación</button>
                <button type="button" class="${MENU_ITEM} wall-post-menu-action" role="menuitem" data-post-menu-action="copy" data-post-id="${post.id}">Copiar enlace</button>
                ${editItem}
            </div>
        </div>`;
}

function closeAllPostMenus(root = document) {
    root.querySelectorAll('[data-post-menu]').forEach((menu) => {
        const trigger = menu.querySelector('[data-post-menu-trigger]');
        const panel = menu.querySelector('.post-card-menu-panel');
        if (trigger) {
            trigger.setAttribute('aria-expanded', 'false');
        }
        panel?.classList.add('hidden');
    });
}

function openPostMenu(menuEl) {
    closeAllPostMenus(document);
    const trigger = menuEl.querySelector('[data-post-menu-trigger]');
    const panel = menuEl.querySelector('.post-card-menu-panel');
    if (!panel) {
        return;
    }
    panel.classList.remove('hidden');
    trigger?.setAttribute('aria-expanded', 'true');
}

/** @type {boolean} */
let postCardMenuGlobalsBound = false;

/**
 * @param {HTMLElement | Document} root
 * @param {{
 *   onOpenDetail?: (postId: number) => void,
 *   onEditPost?: (postId: number) => void,
 *   onNotify?: (message: string, variant?: string) => void,
 *   loginUrl?: string,
 *   isAuthenticated?: boolean
 * }} opts
 */
export function initPostCardMenus(root = document, opts = {}) {
    const host = root instanceof Document ? root.documentElement : root;
    if (!(host instanceof HTMLElement) || host.dataset.postCardMenusBound === '1') {
        return;
    }

    host.dataset.postCardMenusBound = '1';

    host.addEventListener('click', (e) => {
        const trigger = e.target.closest('[data-post-menu-trigger]');
        if (trigger) {
            e.preventDefault();
            e.stopPropagation();
            const menu = trigger.closest('[data-post-menu]');
            const panel = menu?.querySelector('.post-card-menu-panel');
            const isOpen = panel && !panel.classList.contains('hidden');
            closeAllPostMenus(document);
            if (menu && !isOpen) {
                openPostMenu(menu);
            }

            return;
        }

        const actionBtn = e.target.closest('[data-post-menu-action]');
        if (!actionBtn?.dataset.postId) {
            return;
        }

        e.preventDefault();
        e.stopPropagation();
        closeAllPostMenus(document);

        const postId = Number(actionBtn.dataset.postId);
        const action = actionBtn.dataset.postMenuAction;

        if (action === 'open') {
            if (opts.onOpenDetail) {
                opts.onOpenDetail(postId);
            } else {
                window.location.href = `/posts/${postId}`;
            }

            return;
        }

        if (action === 'copy') {
            void sharePost(postId, opts.onNotify);

            return;
        }

        if (action === 'edit') {
            if (!opts.isAuthenticated) {
                if (opts.loginUrl) {
                    window.location.href = opts.loginUrl;
                }

                return;
            }

            if (opts.onEditPost) {
                opts.onEditPost(postId);
            } else {
                sessionStorage.setItem('es:pending-edit-post', String(postId));
                window.location.href = '/dashboard';
            }
        }
    });

    if (!postCardMenuGlobalsBound) {
        postCardMenuGlobalsBound = true;

        document.addEventListener(
            'click',
            (e) => {
                if (e.target.closest('[data-post-menu]')) {
                    return;
                }
                closeAllPostMenus(document);
            },
            true,
        );

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                closeAllPostMenus(document);
            }
        });
    }
}

export { closeAllPostMenus };
