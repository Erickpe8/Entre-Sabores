@props(['active' => 'profile'])

<nav class="flex flex-wrap gap-2 border-b border-warm-200 pb-4 mb-6" aria-label="Configuración">
    <a
        href="{{ route('settings.profile') }}"
        class="rounded-full px-4 py-2 text-sm font-medium transition {{ $active === 'profile' ? 'bg-emerald-500/20 text-emerald-200 ring-1 ring-emerald-400/40' : 'text-ink-muted hover:text-ink hover:bg-white/5' }}"
    >
        Datos del perfil
    </a>
    <a
        href="{{ route('settings.account') }}"
        class="rounded-full px-4 py-2 text-sm font-medium transition {{ $active === 'account' ? 'bg-emerald-500/20 text-emerald-200 ring-1 ring-emerald-400/40' : 'text-ink-muted hover:text-ink hover:bg-white/5' }}"
    >
        Cuenta y seguridad
    </a>
</nav>
