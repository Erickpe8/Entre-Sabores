@php($navUnreadCount = (int) auth()->user()->unread_notifications_count)

<div
    id="nav-notifications-root"
    class="relative shrink-0"
    data-nav-theme="light"
>
    <button
        type="button"
        id="nav-notifications-btn"
        class="navbar-notifications-btn relative"
        aria-label="Notificaciones{{ $navUnreadCount > 0 ? ' ('.$navUnreadCount.' sin leer)' : '' }}"
        aria-expanded="false"
        aria-haspopup="menu"
        aria-controls="nav-notifications-panel"
        @if ($navUnreadCount > 0) aria-live="polite" @endif
    >
        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
        </svg>
        <span
            id="nav-notifications-badge"
            class="navbar-notifications-badge {{ $navUnreadCount > 0 ? '' : 'hidden' }}"
            data-count="{{ $navUnreadCount }}"
        >{{ $navUnreadCount > 9 ? '9+' : ($navUnreadCount > 0 ? $navUnreadCount : '') }}</span>
    </button>

    <div
        id="nav-notifications-panel"
        class="nav-notifications-panel absolute end-0 z-[60] mt-2 hidden w-[min(calc(100vw-1rem),22rem)] origin-top scale-95 translate-y-1 opacity-0 transition duration-150 ease-out overflow-hidden"
        role="menu"
        aria-label="Lista de notificaciones"
    >
        <div class="flex items-center justify-between border-b border-default px-3 py-2.5">
            <span class="text-sm font-semibold text-heading">Notificaciones</span>
            <button
                type="button"
                id="nav-notifications-mark-all"
                class="text-xs font-medium text-accent-warm transition hover:text-accent-gold focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-accent-warm/30 rounded-base px-1"
            >
                Marcar leídas
            </button>
        </div>
        <div
            id="nav-notifications-loading"
            class="hidden px-4 py-8 text-center text-sm text-muted"
        >
            <span class="inline-block h-6 w-6 animate-spin rounded-full border-2 border-default border-t-accent-warm" aria-hidden="true"></span>
            <span class="mt-2 block">Cargando…</span>
        </div>
        <div
            id="nav-notifications-list"
            class="max-h-[min(55vh,22rem)] overflow-y-auto overscroll-contain scrollbar-transparent"
        ></div>
        <div
            id="nav-notifications-empty"
            class="hidden px-4 py-4"
        >
            <x-ui.empty-state
                illustration="empty-no-notifications"
                title="Sin notificaciones"
                message="No hay notificaciones recientes."
            />
        </div>
    </div>
</div>

<textarea id="nav-notifications-config" class="sr-only" readonly tabindex="-1" aria-hidden="true">{!! json_encode([
    'notificationsUrl' => route('notifications.index', absolute: false),
    'readAllUrl' => route('notifications.read_all', absolute: false),
    'initialUnread' => $navUnreadCount,
    'authUserId' => auth()->id(),
    'defaultAvatarUrl' => \App\Support\Illustrations::defaultAvatarPath(),
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</textarea>
