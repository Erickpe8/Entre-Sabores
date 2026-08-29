import { heartSvgHtml, flashLikeAnimation } from './postCard.js';
import { sharePost } from './postCardShare.js';
import { initPostCardMenus } from './postCardMenu.js';

export { sharePost };

const SAVED_POSTS_KEY = 'es:saved-posts';

function readSavedPostIds() {
    try {
        const parsed = JSON.parse(localStorage.getItem(SAVED_POSTS_KEY) || '[]');

        return Array.isArray(parsed) ? parsed : [];
    } catch {
        return [];
    }
}

export function applyLikeState(postId, liked, likesCount, root = document) {
    root.querySelectorAll(`[data-like-post-id="${postId}"]`).forEach((btn) => {
        btn.dataset.liked = liked ? '1' : '0';
        btn.setAttribute('aria-pressed', liked ? 'true' : 'false');
        btn.classList.toggle('text-danger', liked);
        btn.classList.toggle('hover:text-danger', liked);
        const span = btn.querySelector('[data-like-count]');
        if (span) {
            span.textContent = String(likesCount);
        }
        const wrap = btn.querySelector('.wall-like-svg-wrap');
        if (wrap) {
            wrap.innerHTML = heartSvgHtml(liked);
        }
    });
}

function setLikeButtonsBusy(postId, busy, root = document) {
    root.querySelectorAll(`[data-like-post-id="${postId}"]`).forEach((btn) => {
        btn.dataset.busy = busy ? '1' : '0';
        btn.toggleAttribute('disabled', busy);
        btn.classList.toggle('opacity-70', busy);
        btn.classList.toggle('cursor-wait', busy);
    });
}

/**
 * @param {number} postId
 * @param {{
 *   axios: import('axios').AxiosStatic,
 *   postBaseUrl: string,
 *   isAuthenticated?: boolean,
 *   loginUrl?: string,
 *   onError?: (message: string) => void,
 *   root?: ParentNode
 * }} opts
 */
export async function toggleLike(postId, opts) {
    const root = opts.root ?? document;

    if (!opts.isAuthenticated) {
        if (opts.loginUrl) {
            window.location.href = opts.loginUrl;
        }

        return;
    }

    const probeBtn = root.querySelector(`[data-like-post-id="${postId}"]`);
    if (!probeBtn || probeBtn.dataset.busy === '1') {
        return;
    }

    const previousLiked = probeBtn.dataset.liked === '1';
    const previousCount = Number(probeBtn.querySelector('[data-like-count]')?.textContent || 0);
    const optimisticLiked = !previousLiked;
    const optimisticCount = Math.max(0, previousCount + (optimisticLiked ? 1 : -1));

    setLikeButtonsBusy(postId, true, root);
    applyLikeState(postId, optimisticLiked, optimisticCount, root);

    try {
        const base = String(opts.postBaseUrl).replace(/\/$/, '');
        const { data } = await opts.axios.post(`${base}/${postId}/likes/toggle`);
        applyLikeState(postId, data.liked === true, data.likes_count ?? 0, root);
        if (data.liked === true) {
            flashLikeAnimation(postId);
        }
    } catch (error) {
        applyLikeState(postId, previousLiked, previousCount, root);
        console.error(error);
        opts.onError?.('No pudimos guardar tu «me gusta». Inténtalo de nuevo.');
    } finally {
        setLikeButtonsBusy(postId, false, root);
    }
}

/**
 * @param {number} postId
 * @param {(message: string, variant?: string) => void} [onNotify]
 * @param {ParentNode} [root]
 */
export function toggleSavePost(postId, onNotify, root = document) {
    const saved = readSavedPostIds();
    const id = Number(postId);
    const exists = saved.includes(id);
    const next = exists ? saved.filter((item) => item !== id) : [...saved, id];

    localStorage.setItem(SAVED_POSTS_KEY, JSON.stringify(next));

    root.querySelectorAll(`.wall-save-btn[data-post-id="${postId}"]`).forEach((btn) => {
        btn.classList.toggle('text-accent-gold', !exists);
        btn.classList.toggle('hover:text-accent-gold', !exists);
        btn.setAttribute('aria-pressed', !exists ? 'true' : 'false');
    });

    onNotify?.(exists ? 'Eliminada de guardados' : 'Publicación guardada', 'success');
}

/** @param {ParentNode} [root] */
export function syncSavedPostButtons(root = document) {
    const saved = readSavedPostIds();

    root.querySelectorAll('.wall-save-btn[data-post-id]').forEach((btn) => {
        const id = Number(btn.dataset.postId);
        const isSaved = saved.includes(id);
        btn.classList.toggle('text-accent-gold', isSaved);
        btn.classList.toggle('hover:text-accent-gold', isSaved);
        btn.setAttribute('aria-pressed', isSaved ? 'true' : 'false');
    });
}

/**
 * Delegación de clics en botones de tarjeta de publicación.
 *
 * @param {HTMLElement} root
 * @param {{
 *   axios: import('axios').AxiosStatic,
 *   postBaseUrl: string,
 *   isAuthenticated?: boolean,
 *   loginUrl?: string,
 *   onNotify?: (message: string, variant?: string) => void,
 *   onOpenDetail?: (postId: number) => void,
 *   onEditPost?: (postId: number) => void,
 *   onTagFilter?: (tagName: string) => void
 * }} opts
 */
export function setupPostCardInteractions(root, opts) {
    if (!root || root.dataset.postCardInteractionsBound === '1') {
        return;
    }

    root.dataset.postCardInteractionsBound = '1';

    initPostCardMenus(root, {
        onOpenDetail: opts.onOpenDetail,
        onEditPost: opts.onEditPost,
        onNotify: opts.onNotify,
        loginUrl: opts.loginUrl,
        isAuthenticated: opts.isAuthenticated,
    });

    root.addEventListener('click', (e) => {
        const pill = e.target.closest('.wall-feed-tag-pill');
        if (pill) {
            e.preventDefault();
            e.stopPropagation();
            const name = pill.getAttribute('data-tag-name') || '';
            if (name && opts.onTagFilter) {
                opts.onTagFilter(name);
            }

            return;
        }

        const commentBtn = e.target.closest('.wall-comment-btn');
        if (commentBtn?.dataset.postId) {
            e.preventDefault();
            e.stopPropagation();
            if (opts.onOpenDetail) {
                opts.onOpenDetail(Number(commentBtn.dataset.postId));
            }

            return;
        }

        const shareBtn = e.target.closest('.wall-share-btn');
        if (shareBtn?.dataset.postId) {
            e.preventDefault();
            e.stopPropagation();
            void sharePost(Number(shareBtn.dataset.postId), opts.onNotify);

            return;
        }

        const saveBtn = e.target.closest('.wall-save-btn');
        if (saveBtn?.dataset.postId) {
            e.preventDefault();
            e.stopPropagation();
            toggleSavePost(Number(saveBtn.dataset.postId), opts.onNotify, root);

            return;
        }

        const likeBtn = e.target.closest('.wall-like-btn');
        if (!likeBtn?.dataset.likePostId) {
            return;
        }

        e.preventDefault();
        e.stopPropagation();
        void toggleLike(Number(likeBtn.dataset.likePostId), {
            axios: opts.axios,
            postBaseUrl: opts.postBaseUrl,
            isAuthenticated: opts.isAuthenticated,
            loginUrl: opts.loginUrl,
            onError: (msg) => opts.onNotify?.(msg, 'error'),
            root,
        });
    });
}
