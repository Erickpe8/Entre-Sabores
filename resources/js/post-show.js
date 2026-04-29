import { renderCard, heartSvgHtml, flashLikeAnimation } from './social/postCard.js';
import { renderCommentsTreeHtml, setupCommentInteractions } from './social/commentThread.js';

function showToast(el, message) {
    if (!el) {
        return;
    }
    el.textContent = message;
    el.classList.remove('hidden');
    window.setTimeout(() => {
        el.classList.add('hidden');
    }, 4200);
}

function syncCommentsCount(root, postId, count) {
    root.querySelectorAll(`article[data-post-id="${postId}"] [data-comments-count]`).forEach((node) => {
        node.textContent = String(count);
    });
}

export function initPostShow() {
    const root = document.getElementById('post-show-page');
    if (!root) {
        return;
    }

    const axios = window.axios;
    if (!axios) {
        console.error('Axios no está disponible');

        return;
    }

    const cfgEl = document.getElementById('post-show-config');
    const postEl = document.getElementById('post-show-json');
    if (!cfgEl?.textContent || !postEl?.textContent) {
        return;
    }

    const config = JSON.parse(cfgEl.textContent);
    const post = JSON.parse(postEl.textContent);

    const mount = document.getElementById('post-show-card-mount');
    if (mount) {
        mount.innerHTML = '';
        mount.appendChild(renderCard(post));
    }

    const commentsWrap = document.getElementById('post-show-comments');
    if (commentsWrap) {
        commentsWrap.className =
            'mt-4 wall-modal-comments-scroll max-h-[min(480px,60vh)] overflow-y-auto overflow-x-hidden rounded-xl border border-slate-700/60 bg-slate-950/50 p-3 shadow-inner';
        commentsWrap.innerHTML = renderCommentsTreeHtml(post.comments || [], {
            showReplyButtons: config.isAuthenticated === true,
        });
    }

    const toastEl = document.getElementById('post-show-toast');

    async function toggleLike(postId) {
        if (!config.isAuthenticated) {
            window.location.href = config.loginUrl;

            return;
        }
        try {
            const { data } = await axios.post(`${config.postBaseUrl}/${postId}/likes/toggle`);
            const liked = data.liked === true;
            const likesCount = data.likes_count ?? 0;
            if (liked) {
                flashLikeAnimation(postId);
            }
            root.querySelectorAll(`[data-like-post-id="${postId}"]`).forEach((btn) => {
                btn.dataset.liked = liked ? '1' : '0';
                btn.setAttribute('aria-pressed', liked ? 'true' : 'false');
                btn.classList.toggle('text-rose-500', liked);
                btn.classList.toggle('text-slate-500', !liked);
                const span = btn.querySelector('[data-like-count]');
                if (span) {
                    span.textContent = String(likesCount);
                }
                const wrap = btn.querySelector('.wall-like-svg-wrap');
                if (wrap) {
                    wrap.innerHTML = heartSvgHtml(liked);
                }
            });
        } catch (e) {
            console.error(e);
            showToast(toastEl, 'No se pudo actualizar el me gusta.');
        }
    }

    root.addEventListener('click', (e) => {
        const likeBtn = e.target.closest('.wall-like-btn');
        if (!likeBtn?.dataset.likePostId) {
            return;
        }
        e.preventDefault();
        void toggleLike(Number(likeBtn.dataset.likePostId));
    });

    setupCommentInteractions(root, {
        postId: Number(post.id),
        postBaseUrl: config.postBaseUrl,
        axios,
        isAuthenticated: config.isAuthenticated === true,
        loginUrl: config.loginUrl,
        getCommentsWrap: () => document.getElementById('post-show-comments'),
        showToast: (msg) => showToast(toastEl, msg),
        applyCommentsCount: (pid, count) => syncCommentsCount(root, pid, count),
        updateHeadingCount: (count) => {
            const n = document.getElementById('post-show-heading-count');
            if (n) {
                n.textContent = `(${count})`;
            }
        },
    });
}
