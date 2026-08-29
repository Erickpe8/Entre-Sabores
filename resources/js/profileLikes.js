import { renderCard } from './social/postCard.js';
import { setupPostCardInteractions, syncSavedPostButtons } from './social/postCardInteractions.js';
import { initPostCardMedia } from './social/postCardMedia.js';

/** @type {boolean} */
let initialized = false;

/**
 * Publicaciones marcadas con «me gusta» (solo propietario del perfil; se invoca al abrir la pestaña).
 */
export function initProfileLikes() {
    if (initialized) {
        return;
    }

    const grid = document.getElementById('profile-likes-grid');
    const cfgEl = document.getElementById('profile-likes-config');
    const statusEl = document.getElementById('profile-likes-status');
    const sentinel = document.getElementById('profile-likes-sentinel');

    if (!grid || !cfgEl?.textContent) {
        return;
    }

    initialized = true;

    const axios = window.axios;
    if (!axios) {
        return;
    }

    const config = JSON.parse(cfgEl.textContent);
    const postBaseUrl = config.postBaseUrl || config.postPublicBase || '/posts';

    setupPostCardInteractions(grid, {
        axios,
        postBaseUrl,
        isAuthenticated: config.isAuthenticated === true,
        loginUrl: config.loginUrl,
        onOpenDetail: (id) => {
            window.location.href = `${config.postPublicBase}/${id}`;
        },
    });
    initPostCardMedia(document);

    const state = {
        page: 1,
        loading: false,
        hasMore: true,
    };

    function setStatus(text) {
        if (statusEl) {
            statusEl.textContent = text;
        }
    }

    async function loadPage(append = false) {
        if (state.loading || !state.hasMore) {
            return;
        }
        state.loading = true;
        setStatus(append ? 'Cargando más…' : 'Cargando…');

        try {
            const url = new URL(config.likesUrl, window.location.origin);
            url.searchParams.set('page', String(state.page));
            url.searchParams.set('per_page', '10');
            const { data } = await axios.get(url.toString());

            const posts = data.posts || [];
            const meta = data.meta || {};

            if (!append) {
                grid.innerHTML = '';
            }

            posts.forEach((post) => {
                const el = renderCard(post, {
                    onOpenDetail: (id) => {
                        window.location.href = `${config.postPublicBase}/${id}`;
                    },
                });
                grid.appendChild(el);
            });

            syncSavedPostButtons(grid);

            state.hasMore = meta.has_more === true;
            if (state.hasMore) {
                state.page = (meta.current_page || state.page) + 1;
            }

            if (grid.children.length === 0) {
                grid.innerHTML = `
                    <div class="col-span-full rounded-xl border border-white/10 bg-white/[0.03] px-5 py-10 text-center">
                        <p class="text-sm leading-relaxed text-ink-secondary">
                            Aún no tienes publicaciones marcadas como favoritas. Explora y guarda las que más te gusten.
                        </p>
                    </div>`;
            }
            setStatus(
                state.hasMore
                    ? 'Desplázate para cargar más.'
                    : grid.querySelector('.col-span-full')
                      ? ''
                      : '',
            );
        } catch (e) {
            console.error(e);
            setStatus('No se pudieron cargar tus «me gusta». Inténtalo de nuevo.');
        } finally {
            state.loading = false;
        }
    }

    void loadPage(false);

    if (sentinel && 'IntersectionObserver' in window) {
        const obs = new IntersectionObserver(
            (entries) => {
                if (!entries[0]?.isIntersecting) {
                    return;
                }
                void loadPage(true);
            },
            { rootMargin: '200px' },
        );
        obs.observe(sentinel);
    }
}
