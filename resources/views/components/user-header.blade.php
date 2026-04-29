@props([
    'user',
    'viewerFollows' => false,
    'postsCount' => 0,
    'likesReceived' => 0,
    'followersCount' => 0,
    'followingCount' => 0,
])

@php
    $preferenceIcons = [
        'Amante del vino' => 'glass-water',
        'Café lover' => 'coffee',
        'Comida rápida' => 'pizza',
        'Gastronomía gourmet' => 'utensils',
        'Street food' => 'sandwich',
        'Postres' => 'cake',
        'Comida tradicional' => 'soup',
        'Explorador culinario' => 'compass',
    ];
@endphp

@php
    $profileUrl = route('profile.show', ['username' => $user->username]);
    $qrUrl = 'https://quickchart.io/qr?size=360&text='.urlencode($profileUrl);
@endphp

<div {{ $attributes->merge(['class' => 'relative flex flex-col items-center text-center space-y-3']) }}>
    <div class="absolute right-0 top-0 z-10 flex items-center gap-2">
        @auth
            @if (auth()->id() === $user->id)
                <a
                    href="{{ route('settings.profile') }}"
                    class="inline-flex h-9 w-9 items-center justify-center rounded-full border border-white/15 bg-white/5 text-slate-200 transition hover:bg-white/10 hover:text-white focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-400/60"
                    aria-label="Editar perfil"
                >
                    <i data-lucide="pencil" class="h-4 w-4" aria-hidden="true"></i>
                </a>
            @endif
        @endauth

        <button
            type="button"
            id="profile-share-open"
            class="inline-flex h-9 w-9 items-center justify-center rounded-full border border-white/15 bg-white/5 text-slate-200 transition hover:bg-white/10 hover:text-white focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-400/60"
            aria-label="Compartir perfil"
        >
            <i data-lucide="send" class="h-4 w-4" aria-hidden="true"></i>
        </button>
    </div>

    <x-user-profile-card
        :user="$user"
        :show-country="false"
        class="w-full"
    />

    <div class="space-y-1 w-full">

        <x-user-stats
            class="mt-2"
            :posts-count="$postsCount"
            :likes-received="$likesReceived"
            :followers-count="$followersCount"
            :following-count="$followingCount"
        />

        @auth
            @if (auth()->id() !== $user->id)
                <button
                    type="button"
                    id="profile-follow-btn"
                    data-following="{{ $viewerFollows ? '1' : '0' }}"
                    class="mt-4 w-full rounded-full px-4 py-2.5 text-sm font-semibold transition border {{ $viewerFollows ? 'border-white/30 bg-white/10 text-white hover:bg-white/15' : 'border-green-400 bg-green-500 text-white hover:bg-green-400' }}"
                >
                    {{ $viewerFollows ? 'Dejar de seguir' : 'Seguir' }}
                </button>
            @endif
        @else
            <a
                href="{{ route('login', [], false) }}"
                class="mt-4 inline-flex w-full justify-center rounded-full border border-green-400 bg-green-500 px-4 py-2.5 text-sm font-semibold text-white hover:bg-green-400 transition"
            >
                Inicia sesión para seguir
            </a>
        @endauth

        <div class="flex items-center justify-center gap-2 text-gray-400 text-sm mt-4 pt-4 border-t border-white/10">
            <i data-lucide="map-pin" class="w-4 h-4 text-green-400 shrink-0" aria-hidden="true"></i>
            <span>{{ $user->country ?? '—' }}</span>
        </div>
    </div>

    <div class="mt-3 w-full space-y-3 text-sm text-gray-400 border-t border-white/10 pt-4">
        @if ($user->birthdate)
            <div class="flex items-center justify-center gap-2">
                <i data-lucide="cake" class="w-4 h-4 text-green-400 shrink-0" aria-hidden="true"></i>
                <span class="text-gray-300">{{ $user->birthdate->age }} años</span>
            </div>
        @endif

        <div class="flex items-center justify-center gap-2 text-center">
            <i data-lucide="calendar" class="w-4 h-4 text-green-400 shrink-0" aria-hidden="true"></i>
            <span>
                Miembro desde
                <span class="text-gray-300 font-medium">
                    {{ $user->created_at->locale(app()->getLocale())->translatedFormat('F Y') }}
                </span>
            </span>
        </div>
    </div>

    @if ($user->preferences && count($user->preferences))
        <div class="mt-3 flex flex-wrap gap-2 justify-center w-full">
            @foreach ($user->preferences as $pref)
                <span class="inline-flex items-center gap-1.5 px-3 py-1 text-xs rounded-full bg-green-400/20 text-green-300 border border-green-400/30">
                    <i data-lucide="{{ $preferenceIcons[$pref] ?? 'star' }}" class="w-3.5 h-3.5 text-green-400 shrink-0" aria-hidden="true"></i>
                    <span>{{ $pref }}</span>
                </span>
            @endforeach
        </div>
    @endif

    <div class="mt-3 flex flex-wrap gap-4 justify-center items-center w-full border-t border-white/10 pt-4">
        @if ($user->instagram)
            <a
                href="https://www.instagram.com/{{ $user->instagram }}/"
                target="_blank"
                rel="noopener noreferrer"
                class="flex items-center gap-2 text-pink-400 hover:scale-105 transition"
            >
                <i class="fab fa-instagram text-lg" aria-hidden="true"></i>
                <span class="text-sm">{{ '@'.$user->instagram }}</span>
            </a>
        @endif

        @if ($user->linkedin)
            <a
                href="https://www.linkedin.com/in/{{ $user->linkedin }}/"
                target="_blank"
                rel="noopener noreferrer"
                class="flex items-center gap-2 text-sky-400 hover:scale-105 transition"
            >
                <i class="fab fa-linkedin text-lg" aria-hidden="true"></i>
                <span class="text-sm">{{ $user->linkedin }}</span>
            </a>
        @endif
    </div>
</div>

<div id="profile-share-modal" class="fixed inset-0 z-50 hidden items-center justify-center p-4 sm:p-6" role="dialog" aria-modal="true" aria-labelledby="profile-share-title">
    <button type="button" id="profile-share-backdrop" class="absolute inset-0 bg-black/70 backdrop-blur-sm" aria-label="Cerrar compartir"></button>
    <div class="relative w-full max-w-sm overflow-hidden rounded-2xl bg-slate-50 p-5 shadow-2xl ring-1 ring-black/10">
        <button type="button" id="profile-share-close" class="absolute right-2 top-2 inline-flex h-8 w-8 items-center justify-center rounded-full text-slate-500 transition hover:bg-slate-200 hover:text-slate-700" aria-label="Cerrar">
            <i data-lucide="x" class="h-4 w-4" aria-hidden="true"></i>
        </button>

        <div class="space-y-3 text-center">
            <h3 id="profile-share-title" class="text-base font-semibold text-slate-900">Compartir perfil</h3>
            <p class="text-sm text-slate-500">{{ '@'.$user->username }}</p>
            <div class="mx-auto w-fit rounded-xl bg-white p-2 shadow ring-1 ring-slate-200">
                <img id="profile-share-qr" src="{{ $qrUrl }}" alt="QR del perfil" class="h-52 w-52 rounded-lg object-contain" loading="lazy" />
            </div>
            <p class="truncate text-xs text-slate-500" id="profile-share-url">{{ $profileUrl }}</p>
        </div>

        <div class="mt-4 grid grid-cols-3 gap-2">
            <button type="button" id="profile-share-native" class="rounded-lg bg-slate-900 px-3 py-2 text-xs font-medium text-white transition hover:bg-slate-800">Compartir</button>
            <button type="button" id="profile-share-copy" class="rounded-lg bg-slate-200 px-3 py-2 text-xs font-medium text-slate-700 transition hover:bg-slate-300">Copiar enlace</button>
            <a id="profile-share-download" href="{{ $qrUrl }}" download="perfil-{{ $user->username }}-qr.png" target="_blank" rel="noopener noreferrer" class="inline-flex items-center justify-center rounded-lg bg-emerald-600 px-3 py-2 text-xs font-medium text-white transition hover:bg-emerald-500">Descargar</a>
        </div>
    </div>
</div>

@once
    <script>
        (function () {
            const modal = document.getElementById('profile-share-modal');
            const openBtn = document.getElementById('profile-share-open');
            const closeBtn = document.getElementById('profile-share-close');
            const backdrop = document.getElementById('profile-share-backdrop');
            const copyBtn = document.getElementById('profile-share-copy');
            const shareBtn = document.getElementById('profile-share-native');
            const urlEl = document.getElementById('profile-share-url');

            if (!modal || !openBtn || !urlEl) return;
            const shareUrl = urlEl.textContent?.trim() || window.location.href;

            const openModal = () => {
                modal.classList.remove('hidden');
                modal.classList.add('flex');
                document.body.classList.add('overflow-hidden');
            };

            const closeModal = () => {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
                document.body.classList.remove('overflow-hidden');
            };

            openBtn.addEventListener('click', openModal);
            closeBtn?.addEventListener('click', closeModal);
            backdrop?.addEventListener('click', closeModal);

            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && !modal.classList.contains('hidden')) {
                    closeModal();
                }
            });

            copyBtn?.addEventListener('click', async () => {
                try {
                    await navigator.clipboard.writeText(shareUrl);
                    copyBtn.textContent = 'Copiado';
                    window.setTimeout(() => {
                        copyBtn.textContent = 'Copiar enlace';
                    }, 1200);
                } catch (err) {
                    console.error(err);
                }
            });

            shareBtn?.addEventListener('click', async () => {
                try {
                    if (navigator.share) {
                        await navigator.share({
                            title: 'Perfil de Entre Sabores',
                            text: 'Mira este perfil en Entre Sabores',
                            url: shareUrl,
                        });
                        return;
                    }
                    await navigator.clipboard.writeText(shareUrl);
                    shareBtn.textContent = 'Copiado';
                    window.setTimeout(() => {
                        shareBtn.textContent = 'Compartir';
                    }, 1200);
                } catch (err) {
                    console.error(err);
                }
            });
        })();
    </script>
@endonce
