@php($navUnreadCount = (int) auth()->user()->unread_notifications_count)
@php($navDark = request()->routeIs('dashboard') || request()->routeIs('settings.profile') || request()->routeIs('settings.account') || request()->routeIs('profile.show'))

<div
    id="nav-notifications-root"
    class="relative shrink-0"
    data-nav-theme="{{ $navDark ? 'dark' : 'light' }}"
    x-data="{ open: false }"
    @click.outside="open = false"
    @keydown.escape.window="open = false"
>
    <button
        type="button"
        id="nav-notifications-btn"
        @click="open = ! open"
        :aria-expanded="open ? 'true' : 'false'"
        class="relative inline-flex h-10 w-10 min-h-[40px] min-w-[40px] items-center justify-center rounded-full border transition active:scale-95 {{ $navDark ? 'border-white/15 bg-white/5 text-slate-200 hover:bg-white/10 hover:text-white' : 'border-stone-200 bg-white text-stone-600 hover:bg-stone-50' }}"
        aria-label="Notificaciones"
    >
        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
        </svg>
        <span
            id="nav-notifications-badge"
            class="absolute -top-0.5 -end-0.5 flex h-[1.125rem] min-w-[1.125rem] items-center justify-center rounded-full bg-rose-500 px-1 text-[10px] font-bold leading-none text-white {{ $navUnreadCount > 0 ? '' : 'hidden' }}"
            data-count="{{ $navUnreadCount }}"
        >{{ $navUnreadCount > 9 ? '9+' : $navUnreadCount }}</span>
    </button>

    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 scale-95 translate-y-1"
        x-transition:enter-end="opacity-100 scale-100 translate-y-0"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="absolute end-0 z-[60] mt-2 w-[min(calc(100vw-1rem),22rem)] overflow-hidden rounded-xl border shadow-xl {{ $navDark ? 'border-slate-700 bg-slate-900' : 'border-stone-200 bg-white' }}"
        style="display: none;"
        role="menu"
        aria-label="Lista de notificaciones"
        @click.stop
    >
        <div class="flex items-center justify-between border-b px-3 py-2 {{ $navDark ? 'border-slate-700' : 'border-stone-200' }}">
            <span class="text-sm font-semibold {{ $navDark ? 'text-slate-100' : 'text-stone-900' }}">Notificaciones</span>
            <button
                type="button"
                id="nav-notifications-mark-all"
                class="text-xs font-medium text-emerald-500 hover:text-emerald-400 hover:underline"
            >
                Marcar leídas
            </button>
        </div>
        <div
            id="nav-notifications-loading"
            class="hidden px-4 py-8 text-center text-sm {{ $navDark ? 'text-slate-500' : 'text-stone-500' }}"
        >
            <span class="inline-block h-6 w-6 animate-spin rounded-full border-2 border-emerald-500/30 border-t-emerald-500" aria-hidden="true"></span>
            <span class="mt-2 block">Cargando…</span>
        </div>
        <div
            id="nav-notifications-list"
            class="max-h-[min(55vh,22rem)] overflow-y-auto overscroll-contain"
        ></div>
        <p
            id="nav-notifications-empty"
            class="hidden px-4 py-8 text-center text-sm {{ $navDark ? 'text-slate-500' : 'text-stone-500' }}"
        >
            No hay notificaciones recientes.
        </p>
    </div>
</div>

<script type="application/json" id="nav-notifications-config">{!! json_encode([
    'notificationsUrl' => route('notifications.index'),
    'readAllUrl' => route('notifications.read_all'),
    'initialUnread' => $navUnreadCount,
    'authUserId' => auth()->id(),
]) !!}</script>
