import { esc, relativeTimeEs } from './postCard.js';

/**
 * @param {string|undefined} iso
 * @returns {{ relative: string, absolute: string }}
 */
export function commentTimeDetail(iso) {
    if (!iso) {
        return { relative: '', absolute: '' };
    }
    const d = new Date(iso);
    if (Number.isNaN(d.getTime())) {
        return { relative: '', absolute: '' };
    }
    const absolute = d.toLocaleString('es', { dateStyle: 'medium', timeStyle: 'short' });

    return { relative: relativeTimeEs(iso), absolute };
}

/**
 * @param {object[]} comments
 * @param {{ showReplyButtons: boolean }} opts
 */
export function renderCommentsTreeHtml(comments, opts) {
    const showReplyButtons = opts.showReplyButtons === true;
    if (!comments?.length) {
        return '<p class="text-sm text-slate-500 px-1 py-3 text-center border border-dashed border-slate-700/80 rounded-lg">Sin comentarios aún.</p>';
    }

    return `<div class="space-y-3">${comments.map((c) => renderCommentNodeHtml(c, showReplyButtons)).join('')}</div>`;
}

/**
 * @param {object} c
 * @param {boolean} showReplyButtons
 */
export function renderCommentNodeHtml(c, showReplyButtons) {
    const { relative, absolute } = commentTimeDetail(c.created_at);
    const replyBtn = showReplyButtons
        ? `<button type="button" class="wall-comment-reply-btn text-xs font-medium text-emerald-400 hover:text-emerald-300 hover:underline" data-comment-id="${c.id}" data-comment-username="${esc(c.user.username)}">Responder</button>`
        : '';

    const repliesHtml =
        c.replies?.length > 0
            ? `<div class="wall-comment-replies mt-3 space-y-3 border-l-2 border-emerald-500/25 pl-3 ml-0.5">${c.replies.map((r) => renderCommentNodeHtml(r, showReplyButtons)).join('')}</div>`
            : '';

    return `
<article class="wall-comment-node rounded-xl bg-slate-800/95 border border-slate-700/80 p-3 text-sm shadow-sm" data-comment-id="${c.id}">
    <div class="flex items-start justify-between gap-2 mb-2">
        <div class="flex items-center gap-2 min-w-0">
            <img src="${esc(c.user.avatar)}" alt="" width="32" height="32" loading="lazy" class="h-8 w-8 rounded-full object-cover ring-1 ring-slate-600 shrink-0" />
            <div class="min-w-0">
                <div class="font-medium text-slate-100 truncate">${esc(c.user.name)}</div>
                <div class="text-xs text-slate-500 truncate">@${esc(c.user.username)}</div>
            </div>
        </div>
        <time class="text-[11px] text-slate-400 shrink-0 tabular-nums text-right max-w-[min(100%,9rem)] leading-tight" datetime="${esc(c.created_at || '')}" title="${esc(absolute)}">${esc(relative)}</time>
    </div>
    <p class="text-slate-300 whitespace-pre-wrap break-words">${formatCommentBodyHtml(c.body)}</p>
    <div class="mt-2 flex flex-wrap items-center gap-2">${replyBtn}</div>
    ${repliesHtml}
</article>`;
}

/**
 * Resalta menciones @usuario y las convierte en enlace al perfil.
 * @param {string} text
 * @returns {string}
 */
function formatCommentBodyHtml(text) {
    const source = String(text || '');
    const regex = /(^|[\s([{"'])@([a-z0-9_-]{3,30})/gi;
    let out = '';
    let last = 0;
    let match;
    while ((match = regex.exec(source)) !== null) {
        const [full, prefix, username] = match;
        const start = match.index;
        out += esc(source.slice(last, start));
        out += esc(prefix);
        out += `<a href="/profile/${encodeURIComponent(username)}" class="font-medium text-emerald-300 transition hover:text-emerald-200 hover:underline">@${esc(username)}</a>`;
        last = start + full.length;
    }
    out += esc(source.slice(last));

    return out;
}

/**
 * @param {HTMLElement} rootEl
 * @param {{
 *   postId: number,
 *   postBaseUrl: string,
 *   axios: import('axios').AxiosStatic,
 *   isAuthenticated: boolean,
 *   loginUrl: string,
 *   getCommentsWrap: () => HTMLElement | null,
 *   showToast?: (message: string, variant?: string) => void,
 *   applyCommentsCount?: (postId: number, count: number) => void,
 *   afterCommentPosted?: () => void,
 *   updateHeadingCount?: (count: number) => void,
 *   modalCommentsCountSelector?: string,
 * }} ctx
 * @param {{ signal?: AbortSignal }} [options]
 */
export function setupCommentInteractions(rootEl, ctx, options = {}) {
    const listenerOpts = options.signal ? { signal: options.signal } : undefined;
    const submitUrl = `${ctx.postBaseUrl}/${ctx.postId}/comments`;
    const mainCommentForm = rootEl.querySelector('#wall-comment-form');
    const mainCommentTextarea = /** @type {HTMLTextAreaElement|null} */ (
        mainCommentForm?.querySelector('textarea[name="body"]') ?? null
    );
    const replyStateId = 'wall-comment-reply-state';
    const replyParentInputId = 'wall-comment-parent-id';

    function ensureReplyUi() {
        if (!mainCommentForm) {
            return;
        }

        let parentInput = /** @type {HTMLInputElement|null} */ (
            mainCommentForm.querySelector(`#${replyParentInputId}`)
        );
        if (!parentInput) {
            parentInput = document.createElement('input');
            parentInput.type = 'hidden';
            parentInput.name = 'parent_id';
            parentInput.id = replyParentInputId;
            mainCommentForm.appendChild(parentInput);
        }

        let replyState = mainCommentForm.querySelector(`#${replyStateId}`);
        if (!replyState) {
            replyState = document.createElement('div');
            replyState.id = replyStateId;
            replyState.className =
                'hidden items-center justify-between gap-2 rounded-lg border border-emerald-500/30 bg-emerald-500/10 px-3 py-2 text-xs text-emerald-200';
            replyState.innerHTML = `
                <p class="min-w-0 truncate"><span class="text-emerald-300">Respondiendo a</span> <span data-reply-username></span></p>
                <button type="button" class="wall-reply-cancel rounded-full border border-emerald-300/30 px-2.5 py-1 text-[11px] font-medium text-emerald-100 transition hover:bg-emerald-500/20">Cancelar</button>
            `;
            const label = mainCommentForm.querySelector('label');
            if (label && label.parentNode) {
                label.parentNode.insertBefore(replyState, label.nextSibling);
            } else {
                mainCommentForm.prepend(replyState);
            }
        }
    }

    function clearReplyState() {
        if (!mainCommentForm) {
            return;
        }
        const parentInput = /** @type {HTMLInputElement|null} */ (
            mainCommentForm.querySelector(`#${replyParentInputId}`)
        );
        if (parentInput) {
            parentInput.value = '';
        }
        const replyState = mainCommentForm.querySelector(`#${replyStateId}`);
        if (replyState) {
            replyState.classList.add('hidden');
            replyState.classList.remove('flex');
            const usernameEl = replyState.querySelector('[data-reply-username]');
            if (usernameEl) {
                usernameEl.textContent = '';
            }
        }
    }

    function setReplyState(parentId, username) {
        if (!mainCommentForm || !mainCommentTextarea) {
            return;
        }
        ensureReplyUi();
        const parentInput = /** @type {HTMLInputElement|null} */ (
            mainCommentForm.querySelector(`#${replyParentInputId}`)
        );
        const replyState = mainCommentForm.querySelector(`#${replyStateId}`);
        if (!parentInput || !replyState) {
            return;
        }

        parentInput.value = String(parentId);
        const usernameEl = replyState.querySelector('[data-reply-username]');
        if (usernameEl) {
            usernameEl.textContent = `@${username}`;
        }
        replyState.classList.remove('hidden');
        replyState.classList.add('flex');

        const mention = `@${username} `;
        mainCommentTextarea.value = mention;
        mainCommentTextarea.focus();
        const pos = mention.length;
        mainCommentTextarea.setSelectionRange(pos, pos);
        mainCommentTextarea.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }

    async function refreshCommentsTree() {
        const wrap = ctx.getCommentsWrap();
        if (!wrap) {
            return;
        }
        try {
            const { data } = await ctx.axios.get(`${ctx.postBaseUrl}/${ctx.postId}`);
            const p = data.post;
            wrap.innerHTML = renderCommentsTreeHtml(p.comments || [], {
                showReplyButtons: ctx.isAuthenticated,
            });
            if (p.comments_count != null) {
                if (ctx.applyCommentsCount) {
                    ctx.applyCommentsCount(ctx.postId, p.comments_count);
                }
                if (ctx.updateHeadingCount) {
                    ctx.updateHeadingCount(p.comments_count);
                }
                if (ctx.modalCommentsCountSelector) {
                    const el = document.querySelector(ctx.modalCommentsCountSelector);
                    if (el) {
                        el.textContent = String(p.comments_count);
                    }
                }
            }
        } catch (e) {
            console.error(e);
            if (ctx.showToast) {
                ctx.showToast('No pudimos cargar los comentarios actualizados. Actualiza la página si sigue igual.', 'error');
            }
        }
    }

    rootEl.addEventListener('submit', async (e) => {
        const form = e.target;
        if (!(form instanceof HTMLFormElement) || !rootEl.contains(form)) {
            return;
        }

        if (form.id === 'wall-comment-form') {
            e.preventDefault();
            if (!ctx.isAuthenticated) {
                window.location.href = ctx.loginUrl;

                return;
            }
            const fd = new FormData(form);
            const body = String(fd.get('body') || '').trim();
            if (!body) {
                return;
            }
            const parentInput = /** @type {HTMLInputElement|null} */ (
                form.querySelector(`#${replyParentInputId}`)
            );
            const payload = { body };
            if (parentInput?.value) {
                payload.parent_id = Number(parentInput.value);
            }
            try {
                await ctx.axios.post(submitUrl, payload);
                form.reset();
                clearReplyState();
                await refreshCommentsTree();
                if (ctx.showToast) {
                    ctx.showToast(payload.parent_id ? 'Tu respuesta ya está visible.' : 'Tu comentario ya está publicado.', 'success');
                }
                if (ctx.afterCommentPosted) {
                    ctx.afterCommentPosted();
                }
            } catch (err) {
                console.error(err);
                if (ctx.showToast) {
                    ctx.showToast('No pudimos publicar el comentario. Revisa la conexión e inténtalo otra vez.', 'error');
                }
            }

            return;
        }
    }, listenerOpts);

    rootEl.addEventListener('click', (e) => {
        const replyBtn = e.target.closest('.wall-comment-reply-btn');
        if (replyBtn && rootEl.contains(replyBtn)) {
            if (!ctx.isAuthenticated) {
                window.location.href = ctx.loginUrl;

                return;
            }
            const id = replyBtn.dataset.commentId;
            const username = (replyBtn.dataset.commentUsername || '').trim();
            if (!id) {
                return;
            }
            if (!username) {
                return;
            }
            setReplyState(Number(id), username);

            return;
        }

        const cancelBtn = e.target.closest('.wall-reply-cancel');
        if (cancelBtn && rootEl.contains(cancelBtn)) {
            clearReplyState();
            mainCommentTextarea?.focus();
        }
    }, listenerOpts);
}
