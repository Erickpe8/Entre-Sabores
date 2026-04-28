const ExperienceLabels = {
    tradicional: 'Tradicional',
    callejero: 'Callejero',
    gourmet: 'Gourmet',
    dulce: 'Dulce',
    salado: 'Salado',
};

const DrinkLabels = {
    cafe: 'Café',
    vino: 'Vino',
    cerveza: 'Cerveza',
    tradicional: 'Bebidas tradicionales',
};

/** Gradientes tipo daily.dev para cabecera de card (determinísticos por id) */
const HEADER_GRADIENTS = [
    'from-violet-600 via-purple-600 to-fuchsia-700',
    'from-emerald-600 via-teal-600 to-cyan-700',
    'from-amber-600 via-orange-600 to-rose-700',
    'from-sky-600 via-blue-600 to-indigo-800',
    'from-rose-600 via-pink-600 to-violet-800',
];

function gradientClassForPostId(id) {
    const n = Number(id) || 0;

    return HEADER_GRADIENTS[Math.abs(n) % HEADER_GRADIENTS.length];
}

function relativeTimeEs(iso) {
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

function esc(s) {
    const d = document.createElement('div');
    d.textContent = s;

    return d.innerHTML;
}

function formatStory(text) {
    return esc(String(text)).replace(/\n/g, '<br>');
}

/** Chips del muro (tema oscuro, alineado con Blade) */
const CLS = {
    inactive:
        'wall-chip shrink-0 rounded-full px-3 py-1.5 text-xs font-medium transition bg-slate-800/90 text-slate-300 hover:bg-slate-700 hover:text-white',
    inactiveCountry:
        'wall-chip shrink-0 rounded-full px-3 py-1.5 text-xs font-medium transition bg-slate-800/90 text-slate-300 hover:bg-slate-700 hover:text-white inline-flex items-center gap-1',
    primary:
        'wall-chip shrink-0 rounded-full px-3 py-1.5 text-xs font-medium transition bg-emerald-600/90 text-white shadow-sm shadow-emerald-900/30 hover:bg-emerald-500',
    primaryCountry:
        'wall-chip shrink-0 rounded-full px-3 py-1.5 text-xs font-medium transition bg-emerald-600/90 text-white shadow-sm shadow-emerald-900/30 hover:bg-emerald-500 inline-flex items-center gap-1',
    secondary:
        'wall-chip shrink-0 rounded-full px-3 py-1.5 text-xs font-medium transition bg-emerald-500/15 text-emerald-300 ring-1 ring-emerald-500/35 hover:bg-emerald-500/25',
};

const ICON_COMMENT =
    '<svg class="w-3.5 h-3.5 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>';

export function initWall() {
    const axios = window.axios;
    if (!axios) {
        console.error('Axios no está disponible');

        return;
    }

    const cfgEl = document.getElementById('wall-config');
    if (!cfgEl?.textContent) {
        return;
    }

    const config = JSON.parse(cfgEl.textContent);
    const feedEl = document.getElementById('posts-container');
    const skeleton = document.getElementById('wall-skeleton');
    const modal = document.getElementById('wall-modal');
    const modalBody = document.getElementById('wall-modal-body');
    const modalBackdrop = document.getElementById('wall-modal-backdrop');

    const state = {
        country_id: null,
        following: config.initialFollowing === true,
        experience_type: null,
        drink_type: null,
        sort: 'recent',
    };
    let fetchDebounceTimer = null;
    let activeRequestController = null;

    function syncFeedQueryParam() {
        const url = new URL(window.location.href);
        if (state.following) {
            url.searchParams.set('following', '1');
        } else {
            url.searchParams.delete('following');
        }
        window.history.replaceState({}, '', `${url.pathname}${url.search}`);
    }

    function updateNavbarFeedUi() {
        const slider = document.getElementById('navbar-feed-slider');
        if (slider) {
            slider.style.transform = state.following ? 'translateX(100%)' : 'translateX(0%)';
        }

        document.querySelectorAll('[data-navbar-feed]').forEach((el) => {
            const tab = el.dataset.navbarFeed || '';
            const on =
                (tab === 'fyp' && !state.following) || (tab === 'following' && state.following);
            el.classList.toggle('active', on);
            el.setAttribute('aria-pressed', on ? 'true' : 'false');
        });
    }

    function buildQuery() {
        const p = new URLSearchParams();
        if (state.country_id) {
            p.set('country_id', String(state.country_id));
        }
        if (state.following) {
            p.set('following', '1');
        }
        if (state.experience_type) {
            p.set('experience_type', state.experience_type);
        }
        if (state.drink_type) {
            p.set('drink_type', state.drink_type);
        }
        if (state.sort) {
            p.set('sort', state.sort);
        }

        return p.toString();
    }

    async function fetchBoard() {
        if (activeRequestController) {
            activeRequestController.abort();
        }
        activeRequestController = new AbortController();

        skeleton?.classList.remove('hidden');
        feedEl?.classList.add('opacity-40', 'pointer-events-none');

        try {
            const qs = buildQuery();
            const url = qs ? `${config.filterUrl}?${qs}` : config.filterUrl;
            const { data } = await axios.get(url, { signal: activeRequestController.signal });
            renderFeed(data.posts || [], data.meta);
        } catch (e) {
            if (e?.name === 'CanceledError' || e?.name === 'AbortError') {
                return;
            }
            console.error(e);
            if (feedEl) {
                feedEl.innerHTML =
                    '<p class="text-red-400 col-span-full py-14 text-center text-sm">No se pudo cargar el muro. Intenta de nuevo.</p>';
            }
        } finally {
            skeleton?.classList.add('hidden');
            feedEl?.classList.remove('opacity-40', 'pointer-events-none');
            syncFeedQueryParam();
        }
    }

    function scheduleFetch(delayMs = 180) {
        if (fetchDebounceTimer) {
            window.clearTimeout(fetchDebounceTimer);
        }

        fetchDebounceTimer = window.setTimeout(() => {
            fetchBoard();
        }, delayMs);
    }

    function renderFeed(posts, meta) {
        if (!feedEl) {
            return;
        }

        feedEl.innerHTML = '';

        if (meta?.guest_following) {
            feedEl.innerHTML = `
                <p class="col-span-full text-center text-slate-400 py-16 text-sm max-w-md mx-auto leading-relaxed">
                    <a href="${esc(config.loginUrl)}" class="font-medium text-emerald-400 hover:text-emerald-300 underline underline-offset-2">Inicia sesión</a> para ver publicaciones de quienes sigues.
                </p>
            `;

            return;
        }

        if (!posts.length) {
            feedEl.innerHTML =
                '<p class="col-span-full text-center text-slate-500 py-16 text-sm">No hay publicaciones con estos filtros.</p>';

            return;
        }

        posts.forEach((post) => {
            feedEl.appendChild(renderCard(post));
        });
    }

    function renderCard(post) {
        const el = document.createElement('article');
        el.className =
            'group relative flex flex-col overflow-hidden rounded-xl border border-slate-700/80 bg-slate-900/80 shadow-sm shadow-black/30 transition duration-200 ease-out hover:z-[1] hover:scale-[1.02] hover:shadow-lg hover:shadow-black/40 hover:border-slate-600 cursor-pointer backdrop-blur-sm';
        el.dataset.postId = String(post.id);

        const countryName = post.country?.name ?? '—';
        const flag = post.country?.flag_emoji ?? '';
        const when = relativeTimeEs(post.created_at);
        const metaLine = when ? `${esc(flag)} ${esc(countryName)} · ${esc(when)}` : `${esc(flag)} ${esc(countryName)}`;

        const grad = gradientClassForPostId(post.id);
        const typeLabel = ExperienceLabels[post.experience_type] || post.experience_type || '';

        el.innerHTML = `
            <div class="relative h-[140px] shrink-0 overflow-hidden bg-slate-800">
                <div class="absolute inset-0 bg-gradient-to-br ${grad} opacity-95"></div>
                <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_top_right,_rgba(255,255,255,0.12),_transparent_55%)]"></div>
            </div>
            <div class="flex flex-1 flex-col p-4 pt-3">
                <h3 class="text-[15px] font-semibold leading-snug text-slate-100 line-clamp-2">${esc(post.title)}</h3>
                <p class="mt-2 text-[11px] text-slate-500">${metaLine}</p>
                <p class="mt-3 text-sm leading-relaxed text-slate-400 line-clamp-3">${esc(post.excerpt)}</p>
                <div class="mt-4 space-y-1.5 text-xs text-slate-300">
                    <p class="truncate"><span aria-hidden="true">🍽</span> ${esc(post.food_label)}</p>
                    <p class="truncate"><span aria-hidden="true">🍷</span> ${esc(post.drink_label)}</p>
                </div>
                <div class="mt-4 flex flex-wrap items-center justify-between gap-2 border-t border-slate-700/80 pt-3">
                    <span class="inline-flex max-w-[70%] items-center rounded-full bg-slate-800 px-2.5 py-0.5 text-[11px] font-medium text-emerald-300/95 ring-1 ring-slate-600/80 truncate" title="${esc(typeLabel)}">${esc(typeLabel)}</span>
                    <span class="inline-flex shrink-0 items-center gap-1 text-[11px] text-slate-500" title="Comentarios">
                        ${ICON_COMMENT}<span>${post.comments_count ?? 0}</span>
                    </span>
                </div>
            </div>
        `;

        el.addEventListener('click', (e) => {
            if (e.target.closest('a')) {
                return;
            }
            openModal(post.id);
        });

        return el;
    }

    async function openModal(postId) {
        if (!modal || !modalBody) {
            return;
        }

        modalBody.innerHTML =
            '<div class="flex justify-center py-12 text-slate-400">Cargando…</div>';
        modal.classList.remove('hidden');
        document.body.classList.add('overflow-hidden');

        try {
            const { data } = await axios.get(`${config.postBaseUrl}/${postId}`);
            const p = data.post;
            const countryLine =
                p.country != null
                    ? `<p class="text-sm text-slate-400 mt-2 flex items-center gap-2"><span class="text-lg">${esc(p.country.flag_emoji || '')}</span><span>${esc(p.country.name)}</span></p>`
                    : '';

            const commentsHtml = (p.comments || [])
                .map(
                    (c) => `
                <div class="rounded-xl bg-slate-800/80 border border-slate-700/80 p-3 text-sm">
                    <div class="flex items-center gap-2 mb-1">
                        <img src="${c.user.avatar}" alt="" class="h-8 w-8 rounded-full object-cover ring-1 ring-slate-600" />
                        <div>
                            <div class="font-medium text-slate-100">${esc(c.user.name)}</div>
                            <div class="text-xs text-slate-500">@${esc(c.user.username)}</div>
                        </div>
                    </div>
                    <p class="text-slate-300">${esc(c.body)}</p>
                </div>
            `,
                )
                .join('');

            const commentForm = config.isAuthenticated
                ? `
                <form id="wall-comment-form" class="mt-4 space-y-2">
                    <label class="block text-sm font-medium text-slate-300">Tu comentario</label>
                    <textarea name="body" rows="3" class="w-full rounded-xl border-slate-600 bg-slate-800/80 text-slate-100 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500 placeholder-slate-500" required maxlength="2000" placeholder="Escribe algo…"></textarea>
                    <button type="submit" class="inline-flex items-center px-4 py-2 rounded-full bg-emerald-600 text-white text-sm font-medium hover:bg-emerald-500">Publicar</button>
                </form>
            `
                : `<p class="mt-4 text-sm text-slate-400"><a href="${config.loginUrl}" class="text-emerald-400 hover:text-emerald-300 underline">Inicia sesión</a> para comentar.</p>`;

            modalBody.innerHTML = `
                <div class="space-y-4">
                    <div>
                        <h2 class="text-xl font-bold text-slate-50">${esc(p.title)}</h2>
                        <p class="text-sm text-slate-500 mt-1">${ExperienceLabels[p.experience_type] || ''} · ${DrinkLabels[p.drink_type] || ''}</p>
                        ${countryLine}
                    </div>
                    <div class="flex items-center gap-3">
                        <img src="${p.user.avatar}" alt="" class="h-12 w-12 rounded-full object-cover border border-slate-600" />
                        <div>
                            <div class="font-semibold text-slate-100">${esc(p.user.name)}</div>
                            <a href="${esc(p.user.profile_url)}" class="text-sm text-emerald-400 hover:text-emerald-300 hover:underline">@${esc(p.user.username)}</a>
                        </div>
                    </div>
                    <div class="text-sm leading-relaxed text-slate-300 whitespace-pre-wrap">${formatStory(p.story)}</div>
                    <div class="flex flex-wrap gap-4 text-sm border-t border-slate-700 pt-4 text-slate-300">
                        <span><strong class="text-slate-200">🍽 Comida:</strong> ${esc(p.food_label)}</span>
                        <span><strong class="text-slate-200">🍷 Bebida:</strong> ${esc(p.drink_label)}</span>
                    </div>
                    <div>
                        <h3 class="font-semibold text-slate-200 mb-2">Comentarios (${p.comments_count})</h3>
                        <div id="wall-modal-comments" class="space-y-2 max-h-60 overflow-y-auto">${commentsHtml || '<p class="text-sm text-slate-500">Sin comentarios aún.</p>'}</div>
                        ${commentForm}
                    </div>
                </div>
            `;

            const form = document.getElementById('wall-comment-form');
            if (form) {
                form.addEventListener('submit', async (ev) => {
                    ev.preventDefault();
                    const fd = new FormData(form);
                    const body = String(fd.get('body') || '').trim();
                    if (!body) {
                        return;
                    }
                    try {
                        const res = await axios.post(`${config.postBaseUrl}/${postId}/comments`, { body });
                        const wrap = document.getElementById('wall-modal-comments');
                        if (wrap && res.data.comment) {
                            const c = res.data.comment;
                            const block = document.createElement('div');
                            block.className =
                                'rounded-xl bg-slate-800/80 border border-slate-700/80 p-3 text-sm';
                            block.innerHTML = `
                                <div class="flex items-center gap-2 mb-1">
                                    <img src="${c.user.avatar}" alt="" class="h-8 w-8 rounded-full object-cover ring-1 ring-slate-600" />
                                    <div>
                                        <div class="font-medium text-slate-100">${esc(c.user.name)}</div>
                                        <div class="text-xs text-slate-500">@${esc(c.user.username)}</div>
                                    </div>
                                </div>
                                <p class="text-slate-300">${esc(c.body)}</p>
                            `;
                            wrap.prepend(block);
                            form.reset();
                        }
                        fetchBoard();
                    } catch (err) {
                        console.error(err);
                    }
                });
            }
        } catch (e) {
            modalBody.innerHTML =
                '<p class="text-red-400 text-center py-8">No se pudo cargar la publicación.</p>';
        }
    }

    function closeModal() {
        modal?.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
    }

    document.getElementById('wall-modal-close')?.addEventListener('click', closeModal);
    modalBackdrop?.addEventListener('click', closeModal);

    document.querySelectorAll('[data-navbar-feed]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const tab = btn.dataset.navbarFeed || '';
            if (tab === 'fyp') {
                state.following = false;
                state.country_id = null;
                updateFilterUi();
                scheduleFetch();

                return;
            }
            if (tab === 'following') {
                if (!config.isAuthenticated) {
                    window.location.href = config.loginUrl;

                    return;
                }
                state.following = true;
                state.country_id = null;
                updateFilterUi();
                scheduleFetch();
            }
        });
    });

    document.querySelectorAll('[data-country-chip]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const id = Number(btn.dataset.countryChip);
            if (state.country_id === id) {
                state.country_id = null;
            } else {
                state.country_id = id;
            }
            state.following = false;
            updateFilterUi();
            scheduleFetch();
        });
    });

    document.querySelectorAll('[data-adv-experience]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const v = btn.dataset.advExperience || '';
            state.experience_type = state.experience_type === v ? null : v;
            updateFilterUi();
            scheduleFetch();
        });
    });

    document.querySelectorAll('[data-adv-drink]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const v = btn.dataset.advDrink || '';
            state.drink_type = state.drink_type === v ? null : v;
            updateFilterUi();
            scheduleFetch();
        });
    });

    document.querySelectorAll('[data-sort]').forEach((btn) => {
        btn.addEventListener('click', () => {
            state.sort = btn.dataset.sort || 'recent';
            updateFilterUi();
            scheduleFetch();
        });
    });

    function updateFilterUi() {
        updateNavbarFeedUi();

        document.querySelectorAll('[data-country-chip]').forEach((el) => {
            const id = Number(el.dataset.countryChip);
            const on = !state.following && state.country_id === id;
            el.className = on ? CLS.primaryCountry : CLS.inactiveCountry;
        });

        document.querySelectorAll('[data-adv-experience]').forEach((el) => {
            const v = el.dataset.advExperience;
            const on = state.experience_type === v;
            el.className = on ? CLS.secondary : CLS.inactive;
        });

        document.querySelectorAll('[data-adv-drink]').forEach((el) => {
            const v = el.dataset.advDrink;
            const on = state.drink_type === v;
            el.className = on ? CLS.secondary : CLS.inactive;
        });

        document.querySelectorAll('[data-sort]').forEach((el) => {
            const on = state.sort === el.dataset.sort;
            el.className = on ? CLS.secondary : CLS.inactive;
        });
    }

    updateFilterUi();
    scheduleFetch(0);
}
