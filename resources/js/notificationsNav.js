import { ensureEcho } from './echo.js';

function esc(s) {
    const d = document.createElement('div');
    d.textContent = s;

    return d.innerHTML;
}

function formatNotifTime(iso) {
    if (!iso) {
        return '';
    }
    const d = new Date(iso);
    if (Number.isNaN(d.getTime())) {
        return '';
    }

    return d.toLocaleString('es', { dateStyle: 'short', timeStyle: 'short' });
}

function showRealtimeToast(message, themeDark) {
    const el = document.createElement('div');
    el.setAttribute('role', 'status');
    el.className = themeDark
        ? 'fixed bottom-6 left-1/2 z-[70] max-w-[min(calc(100vw-2rem),22rem)] -translate-x-1/2 rounded-xl border border-emerald-500/40 bg-slate-900/95 px-4 py-3 text-sm text-emerald-50 shadow-lg backdrop-blur-sm'
        : 'fixed bottom-6 left-1/2 z-[70] max-w-[min(calc(100vw-2rem),22rem)] -translate-x-1/2 rounded-xl border border-emerald-200 bg-white px-4 py-3 text-sm text-stone-900 shadow-lg';
    el.textContent = message;
    document.body.appendChild(el);
    window.setTimeout(() => {
        el.remove();
    }, 5200);
}

function resolveAvatarUrl(raw) {
    const value = String(raw || '').trim();
    if (!value) {
        return '/images/default.png';
    }

    const isAbsoluteHttp = /^https?:\/\//i.test(value);
    const isAbsoluteLocal = value.startsWith('/') || value.startsWith('data:image/');
    if (isAbsoluteHttp || isAbsoluteLocal) {
        return value;
    }

    return '/images/default.png';
}

/** Evita doble init si el bundle se cargara dos veces (defensa). */
let notificationsNavInitialized = false;

function updateBadge(badgeEl, count) {
    if (!badgeEl) {
        return;
    }
    badgeEl.dataset.count = String(count);
    if (count <= 0) {
        badgeEl.classList.add('hidden');
        badgeEl.textContent = '0';

        return;
    }
    badgeEl.classList.remove('hidden');
    badgeEl.textContent = count > 9 ? '9+' : String(count);
}

export function initNotificationsNav() {
    const cfgEl = document.getElementById('nav-notifications-config');
    const root = document.getElementById('nav-notifications-root');
    const btn = document.getElementById('nav-notifications-btn');
    const listEl = document.getElementById('nav-notifications-list');
    const emptyEl = document.getElementById('nav-notifications-empty');
    const loadingEl = document.getElementById('nav-notifications-loading');
    const markAllBtn = document.getElementById('nav-notifications-mark-all');
    const badge = document.getElementById('nav-notifications-badge');

    if (!cfgEl?.textContent || !btn || !listEl) {
        return;
    }

    const axios = window.axios;
    if (!axios) {
        return;
    }

    if (notificationsNavInitialized) {
        return;
    }
    notificationsNavInitialized = true;

    const config = JSON.parse(cfgEl.textContent);
    const themeDark = root?.dataset.navTheme !== 'light';
    let loadedOnce = false;
    let loading = false;

    const styles = themeDark
        ? {
              rowBorder: 'border-slate-800/80',
              rowUnread: 'bg-emerald-500/10 hover:bg-emerald-500/15',
              rowRead: 'opacity-75 hover:bg-white/5',
              title: 'text-slate-100',
              body: 'text-slate-400',
              time: 'text-slate-500',
              ring: 'ring-slate-600',
              imgBg: 'bg-slate-800',
          }
        : {
              rowBorder: 'border-stone-100',
              rowUnread: 'bg-emerald-50 hover:bg-emerald-100/80',
              rowRead: 'opacity-80 hover:bg-stone-50',
              title: 'text-stone-900',
              body: 'text-stone-600',
              time: 'text-stone-500',
              ring: 'ring-stone-200',
              imgBg: 'bg-stone-100',
          };

    async function refreshUnreadOnly() {
        try {
            const { data } = await axios.get(config.notificationsUrl, { params: { limit: 1 } });
            if (data.unread_count != null) {
                updateBadge(badge, data.unread_count);
            }
        } catch (e) {
            console.error(e);
        }
    }

    async function loadNotifications() {
        if (loading) {
            return;
        }
        loading = true;
        loadingEl?.classList.remove('hidden');
        emptyEl?.classList.add('hidden');
        try {
            const { data } = await axios.get(config.notificationsUrl, { params: { limit: 25 } });
            updateBadge(badge, data.unread_count ?? 0);
            const items = data.notifications || [];
            if (items.length === 0) {
                listEl.innerHTML = '';
                emptyEl?.classList.remove('hidden');

                return;
            }
            emptyEl?.classList.add('hidden');
            listEl.innerHTML = items
                .map((n) => {
                    const d = n.data || {};
                    const url = esc(d.url || '#');
                    const title = esc(d.title || 'Actividad');
                    const body = esc(d.body || '');
                    const avatarUrl = esc(resolveAvatarUrl(d.actor_avatar));
                    const read = n.read === true;
                    const when = esc(formatNotifTime(n.created_at));
                    const rowVisual = read ? styles.rowRead : styles.rowUnread;

                    return `
<a href="${url}" class="notification-row flex gap-3 px-3 py-3 text-left transition ${rowVisual} border-b ${styles.rowBorder} last:border-0" data-notification-id="${esc(n.id)}">
    <img src="${avatarUrl}" alt="" width="40" height="40" class="h-10 w-10 shrink-0 rounded-full object-cover ring-1 ${styles.ring} ${styles.imgBg}" loading="lazy" />
    <span class="min-w-0 flex-1">
        <span class="block text-sm font-medium ${styles.title} leading-snug">${title}</span>
        <span class="block text-xs ${styles.body} mt-0.5 line-clamp-2">${body}</span>
        <span class="block text-[10px] ${styles.time} mt-1 tabular-nums">${when}</span>
    </span>
</a>`;
                })
                .join('');
        } catch (e) {
            console.error(e);
            listEl.innerHTML = `<p class="px-4 py-6 text-center text-sm ${themeDark ? 'text-red-400' : 'text-red-600'}">No se pudieron cargar las notificaciones.</p>`;
        } finally {
            loadingEl?.classList.add('hidden');
            loading = false;
            loadedOnce = true;
        }
    }

    listEl.addEventListener('click', (e) => {
        const row = e.target.closest('.notification-row');
        if (!row?.dataset.notificationId) {
            return;
        }
        setNotificationsDropdown(false);
        const id = row.dataset.notificationId;
        axios.post(`/notifications/${id}/read`).then(() => refreshUnreadOnly()).catch(() => {});
    });

    const panel = document.getElementById('nav-notifications-panel');
    let dropdownOpen = false;

    function setNotificationsDropdown(open) {
        dropdownOpen = open;
        btn.setAttribute('aria-expanded', open ? 'true' : 'false');
        if (panel) {
            panel.setAttribute('aria-hidden', open ? 'false' : 'true');
        }
        if (!panel) {
            return;
        }
        if (open) {
            panel.classList.remove('hidden');
            requestAnimationFrame(() => {
                panel.classList.remove('opacity-0', 'scale-95', 'translate-y-1');
                panel.classList.add('opacity-100', 'scale-100', 'translate-y-0');
            });
            if (!loadedOnce || listEl.innerHTML.trim() === '') {
                void loadNotifications();
            }

            return;
        }

        panel.classList.remove('opacity-100', 'scale-100', 'translate-y-0');
        panel.classList.add('opacity-0', 'scale-95', 'translate-y-1');
        window.setTimeout(() => {
            if (!dropdownOpen) {
                panel.classList.add('hidden');
            }
        }, 100);
    }

    window.addEventListener('entre-sabores:close-notifications', () => {
        setNotificationsDropdown(false);
    });

    if (panel) {
        panel.setAttribute('aria-hidden', 'true');
    }

    btn.addEventListener('click', (e) => {
        e.stopPropagation();
        setNotificationsDropdown(!dropdownOpen);
    });

    panel?.addEventListener('click', (e) => {
        e.stopPropagation();
    });

    document.addEventListener(
        'click',
        (e) => {
            if (!dropdownOpen || root.contains(e.target)) {
                return;
            }
            setNotificationsDropdown(false);
        },
        true,
    );

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && dropdownOpen) {
            setNotificationsDropdown(false);
        }
    });

    markAllBtn?.addEventListener('click', async (e) => {
        e.preventDefault();
        e.stopPropagation();
        try {
            await axios.post(config.readAllUrl);
            updateBadge(badge, 0);
            await loadNotifications();
        } catch (err) {
            console.error(err);
        }
    });

    window.addEventListener('entre-sabores:notifications-refresh', () => {
        void refreshUnreadOnly();
        if (dropdownOpen) {
            loadedOnce = false;
            void loadNotifications();
        }
    });

    updateBadge(badge, config.initialUnread ?? 0);

    const Echo = ensureEcho();
    const uid = config.authUserId;

    function syncWsIndicator() {
        if (!Echo) {
            return;
        }
        const ok = window.__echoWsOnline === true;
        btn.classList.toggle('opacity-70', !ok);
        btn.title = ok ? 'Notificaciones' : 'Notificaciones (sin tiempo real; sincronización periódica)';
    }

    if (Echo && uid != null) {
        Echo.private(`user.${uid}`).listen('.notification.created', (payload) => {
            const safePayload = payload ?? {};
            updateBadge(badge, safePayload.unread_count ?? 0);
            showRealtimeToast(String(safePayload.title || 'Nueva notificación'), themeDark);
            window.dispatchEvent(new CustomEvent('entre-sabores:notifications-refresh'));
        });

        window.addEventListener(
            'beforeunload',
            () => {
                Echo.leave(`user.${uid}`);
            },
            { once: true },
        );

        syncWsIndicator();
        window.addEventListener('entre-sabores:echo-connection', syncWsIndicator);
    }

    const POLL_MS_WS_UP = 180000;
    const POLL_MS_WS_DOWN = 45000;

    let pollTimer = 0;

    function restartUnreadPolling() {
        window.clearInterval(pollTimer);
        const wsUp = window.__echoWsOnline === true;
        const interval = wsUp ? POLL_MS_WS_UP : POLL_MS_WS_DOWN;
        pollTimer = window.setInterval(() => {
            void refreshUnreadOnly();
        }, interval);
    }

    restartUnreadPolling();
    window.addEventListener('entre-sabores:echo-connection', () => restartUnreadPolling());
}
