import { buildPostImageHtml, enhancePostCardMedia } from './postCardMedia.js';
import { buildPostMenuHtml } from './postCardMenu.js';

function userInitials(user) {
    const fromName = String(user?.name || '').trim();
    const fromUsername = String(user?.username || '').trim();
    const source = fromName || fromUsername || '?';
    const parts = source.split(/\s+/).filter(Boolean);

    if (parts.length >= 2) {
        return (parts[0].charAt(0) + parts[1].charAt(0)).toUpperCase();
    }

    return source.slice(0, 2).toUpperCase();
}

export function relativeTimeEs(iso) {
    if (!iso) {
        return '';
    }
    const then = new Date(iso).getTime();
    if (Number.isNaN(then)) {
        return '';
    }
    const sec = Math.floor((Date.now() - then) / 1000);
    if (sec < 45) {
        return 'ahora';
    }
    const min = Math.floor(sec / 60);
    if (min < 60) {
        return min <= 1 ? 'hace 1 min' : `hace ${min} min`;
    }
    const h = Math.floor(min / 60);
    if (h < 24) {
        return h === 1 ? 'hace 1 h' : `hace ${h} h`;
    }
    const d = Math.floor(h / 24);
    if (d < 7) {
        return d === 1 ? 'hace 1 día' : `hace ${d} días`;
    }
    const w = Math.floor(d / 7);
    if (w < 5) {
        return w === 1 ? 'hace 1 sem' : `hace ${w} sem`;
    }

    return new Date(iso).toLocaleDateString('es', { day: 'numeric', month: 'short' });
}

export function esc(s) {
    const d = document.createElement('div');
    d.textContent = s;

    return d.innerHTML;
}

/**
 * @param {{ name?: string, flag_url?: string | null, iso_code?: string | null } | null | undefined} country
 */
export function countryPreviewMetaHtml(country) {
    const name = esc(country?.name ?? '—');
    let url = country?.flag_url;
    if (!url && country?.iso_code) {
        const code = String(country.iso_code).trim().toLowerCase();
        if (code.length === 2) {
            url = `/flags/${code}.svg`;
        }
    }
    if (url) {
        return `<span class="inline-flex min-w-0 max-w-full items-center gap-1.5 text-muted text-xs"><img src="${esc(url)}" alt="" class="h-3.5 w-5 shrink-0 rounded-sm object-cover object-left" width="20" height="14" loading="lazy" decoding="async" /><span class="truncate">${name}</span></span>`;
    }

    return `<span class="truncate text-muted text-xs">${name}</span>`;
}

export function onEnterOrSpace(event, callback) {
    if (event.key === 'Enter' || event.key === ' ') {
        event.preventDefault();
        callback();
    }
}

export function formatStory(text) {
    return esc(String(text)).replace(/\n/g, '<br>');
}

const ICON_CLASS = 'h-5 w-5 shrink-0';

const INTERACTION_BTN =
    'post-card-interaction-btn inline-flex items-center justify-center gap-1.5 min-h-8 min-w-8 p-1.5 text-body text-sm hover:text-accent-warm focus:outline-none focus:ring-4 focus:ring-neutral-tertiary rounded-base transition-colors duration-150 disabled:cursor-wait disabled:opacity-65';

const TAG_PILL =
    'inline-flex items-center bg-accent-gold-soft text-accent-gold border border-accent-gold-soft rounded-full px-3 py-1 text-xs font-medium cursor-pointer hover:bg-accent-gold-medium transition-colors duration-150 focus:outline-none focus:ring-4 focus:ring-neutral-tertiary';

const READ_MORE_LINK =
    'post-card-read-more inline text-accent-warm font-medium hover:underline focus:outline-none focus:ring-2 focus:ring-neutral-tertiary rounded-sm';

export const ICON_COMMENT = `<svg class="${ICON_CLASS}" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>`;

const ICON_SHARE = `<svg class="${ICON_CLASS}" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/></svg>`;

const ICON_BOOKMARK = `<svg class="${ICON_CLASS}" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/></svg>`;

export function flashLikeAnimation(postId) {
    document.querySelectorAll(`button[data-like-post-id="${postId}"]`).forEach((btn) => {
        btn.classList.remove('wall-like-pop');
        btn.offsetWidth;
        btn.classList.add('wall-like-pop');
        window.setTimeout(() => btn.classList.remove('wall-like-pop'), 480);
    });
}

export function heartSvgHtml(liked) {
    if (liked) {
        return `<svg class="${ICON_CLASS}" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M11.645 20.91l-.007-.003-.022-.012a15.247 15.247 0 01-.383-.218 25.18 25.18 0 01-4.244-3.17C4.688 15.36 2.25 12.174 2.25 8.25 2.25 5.322 4.714 3 7.688 3A5.5 5.5 0 0112 5.052 5.5 5.5 0 0116.313 3c2.973 0 5.437 2.322 5.437 5.25 0 3.925-2.438 7.111-4.739 9.256a25.175 25.175 0 01-4.244 3.17 15.247 15.247 0 01-.383.219l-.022.012-.007.004-.003.001a.752.752 0 01-.704 0l-.003-.001z"/></svg>`;
    }

    return `<svg class="${ICON_CLASS}" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>`;
}

function buildAvatarHtml(user, ariaProfile) {
    const profileUrl = esc(user.profile_url || '#');
    const avatarUrl = user.avatar_thumb || user.avatar;
    const initials = esc(userInitials(user));
    const avatarShell =
        'wall-post-user-avatar flex h-11 w-11 shrink-0 items-center justify-center overflow-hidden rounded-full border border-default bg-neutral-secondary-medium transition-colors hover:border-accent-warm focus:outline-none focus:ring-4 focus:ring-neutral-tertiary';

    if (avatarUrl) {
        return `<a href="${profileUrl}" class="${avatarShell}" data-post-avatar-link aria-label="Ver perfil: ${ariaProfile}">
            <img src="${esc(avatarUrl)}" alt="" class="h-full w-full object-cover" width="44" height="44" loading="lazy" decoding="async" onerror="this.classList.add('hidden');this.parentElement.querySelector('[data-avatar-fallback]')?.classList.remove('hidden')" />
            <span class="hidden flex h-full w-full items-center justify-center text-xs font-semibold text-accent-gold" data-avatar-fallback aria-hidden="true">${initials}</span>
        </a>`;
    }

    return `<a href="${profileUrl}" class="${avatarShell}" data-post-avatar-link aria-label="Ver perfil: ${ariaProfile}">
        <span class="flex h-full w-full items-center justify-center text-xs font-semibold text-accent-gold">${initials}</span>
    </a>`;
}

function buildExcerptHtml(post, detailHref, options = {}) {
    const { omitInteractionBar = false, variant = 'feed' } = options;

    if (variant === 'detail') {
        const body = String(post.description || post.excerpt || '').trim();
        if (!body) {
            return '';
        }

        return `<div class="post-card-body text-body text-sm leading-relaxed whitespace-pre-wrap">${formatStory(body)}</div>`;
    }

    if (omitInteractionBar) {
        return '';
    }

    const excerpt = String(post.excerpt || post.description || '').trim();
    if (!excerpt) {
        return `<p class="mb-3 text-body text-sm leading-relaxed">
            <a href="${detailHref}" class="${READ_MORE_LINK} post-card-detail-link" data-post-detail-link>Ver más →</a>
        </p>`;
    }

    return `<p class="post-card-excerpt mb-3 text-body text-sm leading-relaxed">
        <span class="post-card-excerpt__text line-clamp-3">${esc(excerpt)}</span><a href="${detailHref}" class="${READ_MORE_LINK} post-card-detail-link ms-1" data-post-detail-link>Ver más →</a>
    </p>`;
}

/**
 * @param {object} post
 * @param {{ commentsCountId?: string }} [opts]
 */
export function buildInteractionButtonsHtml(post, opts = {}) {
    const liked = post.liked === true;
    const likesCount = post.likes_count ?? 0;
    const commentsCount = post.comments_count ?? 0;
    const idAttr = opts.commentsCountId ? ` id="${esc(opts.commentsCountId)}"` : '';
    const likeActive = liked ? ' text-danger hover:text-danger' : '';

    return `
        <button type="button" class="${INTERACTION_BTN} wall-like-btn${likeActive}" data-like-post-id="${post.id}" data-liked="${liked ? '1' : '0'}" aria-pressed="${liked ? 'true' : 'false'}" aria-label="Me gusta">
            <span class="wall-like-svg-wrap inline-flex">${heartSvgHtml(liked)}</span>
            <span data-like-count class="tabular-nums">${likesCount}</span>
        </button>
        <button type="button" class="${INTERACTION_BTN} wall-comment-btn" data-post-id="${post.id}" aria-label="Comentar">
            ${ICON_COMMENT}
            <span${idAttr} data-comments-count class="tabular-nums">${commentsCount}</span>
        </button>
        <button type="button" class="${INTERACTION_BTN} wall-share-btn" data-post-id="${post.id}" aria-label="Compartir">
            ${ICON_SHARE}
        </button>`;
}

/**
 * @param {object} post
 */
export function buildSaveButtonHtml(post) {
    return `<button type="button" class="${INTERACTION_BTN} wall-save-btn shrink-0" data-post-id="${post.id}" aria-label="Guardar" aria-pressed="false">
        ${ICON_BOOKMARK}
    </button>`;
}

/**
 * @param {object} post
 * @param {{ commentsCountId?: string }} [opts]
 */
export function buildInteractionStatsHtml(post, opts = {}) {
    return `
        <div class="flex items-center justify-between gap-3">
            <div class="flex min-w-0 flex-wrap items-center gap-4 sm:gap-5">
                ${buildInteractionButtonsHtml(post, opts)}
            </div>
            ${buildSaveButtonHtml(post)}
        </div>`;
}

/**
 * @param {object} post
 * @param {{ onOpenDetail?: (id: number) => void, omitInteractionBar?: boolean, variant?: 'feed' | 'detail' }} [options]
 */
export function renderCard(post, options = {}) {
    const { onOpenDetail, omitInteractionBar, variant = 'feed' } = options;
    const isDetail = variant === 'detail';

    const el = document.createElement('article');
    el.className = isDetail
        ? 'post-card post-card--detail flex w-full flex-col relative'
        : 'post-card bg-neutral-primary-soft flex w-full flex-col p-6 border border-default rounded-base shadow-xs relative transition-colors duration-150';
    if (onOpenDetail) {
        el.classList.add('hover:bg-neutral-secondary-medium', 'cursor-pointer', 'focus-visible:outline-none', 'focus-visible:ring-4', 'focus-visible:ring-neutral-tertiary');
        el.setAttribute('role', 'button');
        el.setAttribute('tabindex', '0');
    }
    el.dataset.postId = String(post.id);
    el.setAttribute('aria-label', `Publicación: ${post.title}`);

    const when = relativeTimeEs(post.created_at);
    const countryHtml = countryPreviewMetaHtml(post.user?.country ?? post.country);
    const user = post.user || {};
    const username = user.username ? esc(String(user.username)) : '';
    const displayUser = username ? `@${username}` : esc(user.name || '—');
    const ariaProfile = esc(user.username ? `@${user.username}` : String(user.name || 'usuario'));
    const profileUrl = esc(user.profile_url || '#');
    const detailHref = `/posts/${post.id}`;

    const highlightBadge =
        post.maridaje_highlighted === true
            ? `<span class="pointer-events-none absolute right-4 top-4 z-10 inline-flex items-center rounded-full border border-accent-gold-soft bg-accent-gold-soft px-2.5 py-1 text-[10px] font-semibold uppercase tracking-wide text-accent-gold" title="Maridaje destacado">Destacado</span>`
            : '';

    const timeLine = when
        ? `<p class="text-muted text-xs"><span aria-hidden="true">· </span><span data-post-relative-time>${esc(when)}</span></p>`
        : '';

    const tagPills = (post.tags || [])
        .slice(0, 5)
        .map(
            (t) =>
                `<button type="button" class="${TAG_PILL} wall-feed-tag-pill" data-tag-name="${esc(t.name)}" aria-label="Filtrar por ${esc(t.name)}">#${esc(t.name)}</button>`,
        )
        .join('');

    const imageBlock = buildPostImageHtml(post, esc);
    const excerptBlock = buildExcerptHtml(post, detailHref, { omitInteractionBar, variant });
    const titleHtml = isDetail
        ? `<h2 class="post-card__title text-xl font-bold leading-snug text-heading">${esc(post.title)}</h2>`
        : `<a href="${detailHref}" class="post-card-detail-link mb-3 block focus:outline-none focus:ring-4 focus:ring-neutral-tertiary rounded-base" data-post-detail-link tabindex="-1">
            <h3 class="line-clamp-2 text-base font-semibold text-heading">${esc(post.title)}</h3>
        </a>`;

    el.innerHTML = `
        ${highlightBadge}
        <header class="post-card__header flex items-center gap-3 ${isDetail ? 'pb-4' : 'pb-3'}">
            ${buildAvatarHtml(user, ariaProfile)}
            <div class="min-w-0 flex-1">
                <a href="${profileUrl}" class="text-heading font-semibold text-sm hover:text-accent-warm focus:outline-none focus:ring-4 focus:ring-neutral-tertiary rounded-base" data-post-avatar-link>${displayUser}</a>
                ${timeLine}
                ${countryHtml ? `<div class="mt-1">${countryHtml}</div>` : ''}
            </div>
            ${buildPostMenuHtml(post)}
        </header>
        ${imageBlock}
        ${isDetail ? `<div class="post-card__content space-y-3">${titleHtml}${tagPills ? `<div class="flex flex-wrap gap-2">${tagPills}</div>` : ''}${excerptBlock}</div>` : `${titleHtml}${excerptBlock}${tagPills ? `<div class="mb-3 flex flex-wrap gap-2">${tagPills}</div>` : ''}`}
        ${
            omitInteractionBar === true
                ? ''
                : `
        <hr class="post-card__divider border-default border-t my-3" />
        ${buildInteractionStatsHtml(post)}`
        }
    `;

    enhancePostCardMedia(el);

    if (onOpenDetail) {
        el.addEventListener('click', (e) => {
            if (
                e.target.closest('a[data-post-avatar-link]') ||
                e.target.closest('.wall-like-btn') ||
                e.target.closest('.wall-comment-btn') ||
                e.target.closest('.wall-share-btn') ||
                e.target.closest('.wall-save-btn') ||
                e.target.closest('.wall-post-menu-btn') ||
                e.target.closest('[data-post-menu]') ||
                e.target.closest('.wall-post-menu-action') ||
                e.target.closest('.wall-feed-tag-pill') ||
                e.target.closest('.post-card-media-open')
            ) {
                return;
            }
            if (e.target.closest('[data-post-detail-link]')) {
                e.preventDefault();
            }
            onOpenDetail(post.id);
        });
        el.addEventListener('keydown', (e) => {
            if (
                e.target.closest('.wall-like-btn') ||
                e.target.closest('.wall-comment-btn') ||
                e.target.closest('.wall-share-btn') ||
                e.target.closest('.wall-save-btn') ||
                e.target.closest('.wall-post-menu-btn') ||
                e.target.closest('[data-post-menu]') ||
                e.target.closest('.wall-post-menu-action') ||
                e.target.closest('.wall-feed-tag-pill') ||
                e.target.closest('[data-post-avatar-link]') ||
                e.target.closest('.post-card-media-open')
            ) {
                return;
            }
            onEnterOrSpace(e, () => onOpenDetail(post.id));
        });
    }

    return el;
}
