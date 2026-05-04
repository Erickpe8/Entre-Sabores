<x-app-layout title="{{ '@'.$user->username }} — {{ config('app.name') }}">
    <div class="min-h-[100dvh] bg-gradient-to-br from-[#020617] via-[#0f172a] to-[#022c22] pb-16">
        <div class="max-w-7xl mx-auto px-6 py-10 grid lg:grid-cols-3 gap-8">
            <aside class="space-y-6 lg:self-start lg:sticky lg:top-24">
                <div class="bg-white/5 backdrop-blur-xl rounded-2xl p-6 shadow-xl border border-white/10">
                    <x-user-header
                        :user="$user"
                        :viewer-follows="$viewerFollows"
                        :posts-count="$postsCount"
                        :likes-received="$likesReceived"
                        :followers-count="$followersCount"
                        :following-count="$followingCount"
                    />
                </div>
            </aside>

            <main class="lg:col-span-2 space-y-6">
                <nav class="flex flex-wrap gap-2 border-b border-white/10 pb-3" aria-label="Secciones del perfil">
                    <button
                        type="button"
                        data-profile-tab="posts"
                        class="profile-tab-btn rounded-full px-4 py-2 text-sm font-medium bg-emerald-500/25 text-emerald-100 ring-1 ring-emerald-400/40"
                    >
                        Publicaciones
                    </button>
                    <button
                        type="button"
                        data-profile-tab="likes"
                        class="profile-tab-btn rounded-full px-4 py-2 text-sm font-medium text-slate-400 hover:text-white hover:bg-white/5"
                    >
                        Me gusta
                    </button>
                </nav>

                <div id="profile-panel-posts" data-profile-panel="posts" class="space-y-6">
                    <textarea id="profile-posts-config" class="sr-only" readonly tabindex="-1" aria-hidden="true">@json($profilePostsConfig)</textarea>

                    <div class="bg-white/5 backdrop-blur rounded-2xl p-6 border border-white/10 shadow-lg shadow-black/20">
                        <h2 class="text-white text-lg font-semibold mb-3">
                            Sobre {{ $user->first_name }}
                        </h2>
                        @if ($user->description)
                            <p class="text-gray-300 leading-relaxed whitespace-pre-line">{{ $user->description }}</p>
                        @else
                            <p class="text-gray-500 text-sm">Sin descripción pública.</p>
                        @endif
                    </div>

                    <div class="bg-white/5 backdrop-blur rounded-2xl p-6 border border-white/10 shadow-lg shadow-black/20">
                        <h2 class="text-white text-lg font-semibold mb-4">
                            Publicaciones
                        </h2>
                        <p id="profile-posts-status" class="text-xs text-gray-500 mb-4" aria-live="polite"></p>
                        <div
                            id="profile-posts-grid"
                            class="grid gap-4 sm:grid-cols-2"
                        ></div>
                        <div id="profile-posts-sentinel" class="h-4 w-full" aria-hidden="true"></div>
                    </div>
                </div>

                <div id="profile-panel-likes" data-profile-panel="likes" class="hidden space-y-6">
                    <div class="bg-white/5 backdrop-blur rounded-2xl p-8 border border-white/10 text-center text-slate-400 text-sm">
                        Próximamente: publicaciones a las que este usuario dio «me gusta».
                    </div>
                </div>

            </main>
        </div>
    </div>

</x-app-layout>
