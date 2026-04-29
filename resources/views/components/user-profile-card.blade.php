@props([
    'user',
    'avatarId' => null,
    'showPublicLink' => false,
    'showCountry' => true,
    'showMemberSince' => false,
    'class' => '',
])

<div {{ $attributes->merge(['class' => "flex flex-col items-center text-center space-y-3 {$class}"]) }}>
    <img
        @if ($avatarId) id="{{ $avatarId }}" @endif
        src="{{ $user->profile_photo_url }}"
        alt=""
        class="w-28 h-28 rounded-full border-2 border-green-400 object-cover shrink-0"
    >

    <div class="space-y-1">
        <h2 class="text-white font-semibold text-lg leading-tight">
            {{ $user->first_name }} {{ $user->last_name }}
        </h2>
        <p class="text-gray-400 text-sm">{{ '@'.$user->username }}</p>

        @if ($showPublicLink)
            <a
                href="{{ route('profile.show', $user->username) }}"
                target="_blank"
                rel="noopener noreferrer"
                class="inline-flex items-center gap-1.5 text-xs text-green-400 hover:text-green-300 hover:underline"
            >
                <i data-lucide="external-link" class="w-3.5 h-3.5 shrink-0" aria-hidden="true"></i>
                Ver perfil público
            </a>
        @endif
    </div>

    @if ($showCountry)
        <div class="flex items-center justify-center gap-2 text-gray-400 text-sm">
            <i data-lucide="map-pin" class="w-4 h-4 text-green-400 shrink-0" aria-hidden="true"></i>
            <span>{{ $user->country ?? '—' }}</span>
        </div>
    @endif

    @if ($showMemberSince)
        <div class="flex items-center justify-center gap-2 text-center text-sm text-gray-400">
            <i data-lucide="calendar" class="w-4 h-4 text-green-400 shrink-0" aria-hidden="true"></i>
            <span>
                Miembro desde
                <span class="text-gray-300 font-medium">
                    {{ $user->created_at->locale(app()->getLocale())->translatedFormat('F Y') }}
                </span>
            </span>
        </div>
    @endif
</div>
