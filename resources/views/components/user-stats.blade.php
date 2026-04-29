@props([
    'postsCount' => 0,
    'likesReceived' => 0,
    'followersCount' => 0,
    'followingCount' => 0,
])

<div {{ $attributes->merge(['class' => 'flex flex-wrap items-center justify-center gap-x-5 gap-y-2 text-sm text-gray-400 mt-2']) }}>
    <span><strong class="text-gray-200 tabular-nums">{{ $postsCount }}</strong> publicaciones</span>
    <span><strong class="text-gray-200 tabular-nums">{{ $likesReceived }}</strong> me gusta recibidos</span>
    <span><strong class="text-gray-200 tabular-nums" id="profile-followers-count">{{ $followersCount }}</strong> seguidores</span>
    <span><strong class="text-gray-200 tabular-nums">{{ $followingCount }}</strong> siguiendo</span>
</div>
