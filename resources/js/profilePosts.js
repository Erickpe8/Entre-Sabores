import { renderCard } from './social/postCard.js';

export function initProfilePosts() {
    const grid = document.getElementById('profile-posts-grid');
    const cfgEl = document.getElementById('profile-posts-config');
    const statusEl = document.getElementById('profile-posts-status');
    const sentinel = document.getElementById('profile-posts-sentinel');

    if (!grid || !cfgEl?.textContent) {
        return;
    }

    const axios = window.axios;
    if (!axios) {
        return;
    }

    const config = JSON.parse(cfgEl.textContent);
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
        setStatus(append ? 'Cargando más…' : 'Cargando publicaciones…');

        try {
            const url = new URL(config.postsUrl, window.location.origin);
            url.searchParams.set('page', String(state.page));
            url.searchParams.set('per_page', '12');
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

            state.hasMore = meta.has_more === true;
            if (state.hasMore) {
                state.page = (meta.current_page || state.page) + 1;
            }

            if (grid.children.length === 0) {
                grid.innerHTML =
                    '<p class="col-span-full text-center text-slate-500 text-sm py-10">Aún no hay publicaciones.</p>';
            }
            setStatus(
                state.hasMore
                    ? 'Desplázate para cargar más.'
                    : grid.children.length
                      ? 'Fin de las publicaciones.'
                      : '',
            );
        } catch (e) {
            console.error(e);
            setStatus('No se pudieron cargar las publicaciones.');
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
