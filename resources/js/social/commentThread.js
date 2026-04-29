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
function renderCommentNodeHtml(c, showReplyButtons) {
    const { relative, absolute } = commentTimeDetail(c.created_at);
    const replyBtn = showReplyButtons
        ? `<button type="button" class="wall-comment-reply-btn text-xs font-medium text-emerald-400 hover:text-emerald-300 hover:underline" data-comment-id="${c.id}">Responder</button>`
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
    <p class="text-slate-300 whitespace-pre-wrap break-words">${esc(c.body)}</p>
    <div class="mt-2 flex flex-wrap items-center gap-2">${replyBtn}</div>
    <div class="wall-reply-slot mt-2 hidden border-t border-slate-700/50 pt-2" data-reply-slot="${c.id}"></div>
    ${repliesHtml}
</article>`;
}

function replyFormHtml(parentId) {
    return `
<form class="wall-reply-form space-y-2" data-parent-id="${parentId}">
    <label class="sr-only">Respuesta</label>
    <textarea name="body" rows="2" required maxlength="2000" placeholder="Escribe tu respuesta…" class="w-full rounded-lg border-slate-600 bg-slate-900/80 text-slate-100 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500 placeholder-slate-500"></textarea>
    <div class="flex flex-wrap gap-2">
        <button type="submit" class="inline-flex items-center rounded-full bg-emerald-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-emerald-500">Publicar respuesta</button>
        <button type="button" class="wall-reply-cancel rounded-full px-3 py-1.5 text-xs text-slate-400 hover:bg-slate-800 hover:text-slate-200">Cancelar</button>
    </div>
</form>`;
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
                ctx.showToast('No se pudieron actualizar los comentarios.', 'error');
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
            try {
                await ctx.axios.post(submitUrl, { body });
                form.reset();
                await refreshCommentsTree();
                if (ctx.showToast) {
                    ctx.showToast('Comentario publicado.', 'success');
                }
                if (ctx.afterCommentPosted) {
                    ctx.afterCommentPosted();
                }
            } catch (err) {
                console.error(err);
                if (ctx.showToast) {
                    ctx.showToast('No se pudo publicar el comentario.', 'error');
                }
            }

            return;
        }

        if (form.classList.contains('wall-reply-form')) {
            e.preventDefault();
            if (!ctx.isAuthenticated) {
                window.location.href = ctx.loginUrl;

                return;
            }
            const parentId = Number(form.dataset.parentId);
            if (Number.isNaN(parentId)) {
                return;
            }
            const fd = new FormData(form);
            const body = String(fd.get('body') || '').trim();
            if (!body) {
                return;
            }
            try {
                await ctx.axios.post(submitUrl, { body, parent_id: parentId });
                const slot = rootEl.querySelector(`[data-reply-slot="${parentId}"]`);
                if (slot) {
                    slot.innerHTML = '';
                    slot.classList.add('hidden');
                    slot.setAttribute('hidden', '');
                }
                await refreshCommentsTree();
                if (ctx.showToast) {
                    ctx.showToast('Respuesta publicada.', 'success');
                }
                if (ctx.afterCommentPosted) {
                    ctx.afterCommentPosted();
                }
            } catch (err) {
                console.error(err);
                if (ctx.showToast) {
                    ctx.showToast('No se pudo publicar la respuesta.', 'error');
                }
            }
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
            if (!id) {
                return;
            }
            const slot = rootEl.querySelector(`[data-reply-slot="${id}"]`);
            if (!slot) {
                return;
            }
            const wasHidden = slot.classList.contains('hidden');
            rootEl.querySelectorAll('.wall-reply-slot').forEach((s) => {
                if (s !== slot) {
                    s.innerHTML = '';
                    s.classList.add('hidden');
                    s.setAttribute('hidden', '');
                }
            });
            if (wasHidden) {
                slot.innerHTML = replyFormHtml(id);
                slot.classList.remove('hidden');
                slot.removeAttribute('hidden');
                const ta = slot.querySelector('textarea');
                ta?.focus();
            } else {
                slot.innerHTML = '';
                slot.classList.add('hidden');
                slot.setAttribute('hidden', '');
            }

            return;
        }

        const cancelBtn = e.target.closest('.wall-reply-cancel');
        if (cancelBtn && rootEl.contains(cancelBtn)) {
            const form = cancelBtn.closest('.wall-reply-form');
            const parentId = form?.dataset.parentId;
            if (!parentId) {
                return;
            }
            const slot = rootEl.querySelector(`[data-reply-slot="${parentId}"]`);
            if (slot) {
                slot.innerHTML = '';
                slot.classList.add('hidden');
                slot.setAttribute('hidden', '');
            }
        }
    }, listenerOpts);
}
