/**
 * Pestañas del perfil público y botón seguir/dejar de seguir.
 */
export function initProfilePage() {
    initProfileTabs();
    initProfileFollowButton();
}

function initProfileTabs() {
    const buttons = document.querySelectorAll('[data-profile-tab]');
    const panels = document.querySelectorAll('[data-profile-panel]');
    if (!buttons.length || !panels.length) {
        return;
    }

    const activate = (id) => {
        panels.forEach((p) => {
            p.classList.toggle('hidden', p.getAttribute('data-profile-panel') !== id);
        });
        buttons.forEach((b) => {
            const on = b.getAttribute('data-profile-tab') === id;
            b.classList.toggle('bg-emerald-500/25', on);
            b.classList.toggle('text-emerald-100', on);
            b.classList.toggle('ring-1', on);
            b.classList.toggle('ring-emerald-400/40', on);
            b.classList.toggle('text-slate-400', !on);
            b.classList.toggle('hover:text-white', !on);
            b.classList.toggle('hover:bg-white/5', !on);
        });
    };

    buttons.forEach((btn) => {
        btn.addEventListener('click', () => activate(btn.getAttribute('data-profile-tab') || 'posts'));
    });
}

function initProfileFollowButton() {
    const btn = document.getElementById('profile-follow-btn');
    if (!btn || !(btn instanceof HTMLButtonElement)) {
        return;
    }

    const storeUrl = btn.dataset.followStoreUrl;
    const destroyUrl = btn.dataset.followDestroyUrl;
    if (!storeUrl || !destroyUrl || !window.axios) {
        return;
    }

    btn.addEventListener('click', async function onFollowClick() {
        const following = btn.dataset.following === '1';

        try {
            const res = following
                ? await window.axios.delete(destroyUrl)
                : await window.axios.post(storeUrl);
            const nowFollowing = res.data.following === true;
            if (nowFollowing && !following) {
                window.dispatchEvent(new CustomEvent('entre-sabores:notifications-refresh'));
            }
            btn.dataset.following = nowFollowing ? '1' : '0';
            const n = document.getElementById('profile-followers-count');
            if (n) {
                n.textContent = String(res.data.followers_count);
            }
            btn.textContent = nowFollowing ? 'Dejar de seguir' : 'Seguir';
            btn.classList.toggle('border-white/30', nowFollowing);
            btn.classList.toggle('bg-white/10', nowFollowing);
            btn.classList.toggle('text-white', nowFollowing);
            btn.classList.toggle('hover:bg-white/15', nowFollowing);
            btn.classList.toggle('border-green-400', !nowFollowing);
            btn.classList.toggle('bg-green-500', !nowFollowing);
            btn.classList.toggle('hover:bg-green-400', !nowFollowing);
        } catch (e) {
            console.error(e);
        }
    });
}
