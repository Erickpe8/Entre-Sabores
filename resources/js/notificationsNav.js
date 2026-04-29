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
                    const read = n.read === true;
                    const when = esc(formatNotifTime(n.created_at));
                    const rowVisual = read ? styles.rowRead : styles.rowUnread;

                    return `
<a href="${url}" class="notification-row flex gap-3 px-3 py-3 text-left transition ${rowVisual} border-b ${styles.rowBorder} last:border-0" data-notification-id="${esc(n.id)}">
    <img src="${esc(d.actor_avatar || '')}" alt="" width="40" height="40" class="h-10 w-10 shrink-0 rounded-full object-cover ring-1 ${styles.ring} ${styles.imgBg}" loading="lazy" />
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
        const id = row.dataset.notificationId;
        axios.patch(`/notifications/${id}/read`).then(() => refreshUnreadOnly()).catch(() => {});
    });

    btn.addEventListener('click', () => {
        window.requestAnimationFrame(() => {
            if (btn.getAttribute('aria-expanded') === 'true') {
                if (!loadedOnce || listEl.innerHTML.trim() === '') {
                    void loadNotifications();
                }
            }
        });
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
        if (btn.getAttribute('aria-expanded') === 'true') {
            loadedOnce = false;
            void loadNotifications();
        }
    });

    updateBadge(badge, config.initialUnread ?? 0);

    window.setInterval(() => {
        void refreshUnreadOnly();
    }, 90000);
}
