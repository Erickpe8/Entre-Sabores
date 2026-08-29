@php
    $avatarStyles = [
        'bg-accent-gold-soft text-heading',
        'bg-neutral-secondary-medium text-heading',
        'bg-accent-cool-soft text-heading',
    ];
    $showOverflowBadge = $totalUsers > $recentMembers->count();
@endphp

<div {{ $attributes->merge(['class' => 'community-social-proof']) }}>
    <div class="community-social-proof__avatars" aria-hidden="true">
        @forelse ($recentMembers as $index => $member)
            <span
                class="community-social-proof__avatar {{ $avatarStyles[$index % count($avatarStyles)] }}"
                title="{{ '@'.$member->username }}"
            >
                @if ($member->profile_photo)
                    <img
                        src="{{ $member->profile_photo_thumb_url }}"
                        alt=""
                        class="h-full w-full object-cover"
                        width="32"
                        height="32"
                        loading="lazy"
                        decoding="async"
                        onerror="this.hidden=true; this.nextElementSibling?.classList.remove('hidden')"
                    />
                    <span class="hidden text-xs font-semibold">{{ $member->initials }}</span>
                @else
                    <span class="text-xs font-semibold">{{ $member->initials }}</span>
                @endif
            </span>
        @empty
            <span class="community-social-proof__avatar bg-neutral-secondary-medium text-muted">?</span>
        @endforelse

        @if ($showOverflowBadge)
            <span class="community-social-proof__avatar community-social-proof__avatar--more">+</span>
        @endif
    </div>

    <p class="community-social-proof__label">
        @if ($totalUsers === 0)
            Sé el primero en unirte
        @else
            <span class="font-semibold text-accent-cool">{{ number_format($totalUsers, 0, ',', '.') }}</span>
            {{ $totalUsers === 1 ? 'gastrónomo ya se unió' : 'gastrónomos ya se unieron' }}
        @endif
    </p>
</div>
