<x-app-layout
    :title="$pageTitle"
    :meta-description="$metaDescription"
    :og-image="$ogImage"
    :og-url="$ogUrl"
>
    <div id="post-show-page" class="min-h-[100dvh] bg-slate-950 text-slate-100">
        <script type="application/json" id="post-show-config">@json($postShowConfig)</script>
        <script type="application/json" id="post-show-json">@json($postPayload)</script>

        <div
            id="post-show-toast"
            class="pointer-events-none fixed top-20 left-1/2 z-[60] hidden max-w-md -translate-x-1/2 rounded-xl border border-emerald-500/40 bg-emerald-950/95 px-4 py-3 text-sm text-emerald-50 shadow-lg backdrop-blur-sm"
            role="status"
            aria-live="polite"
        ></div>

        <div class="mx-auto max-w-3xl px-4 py-8 sm:px-6">
            <a
                href="{{ auth()->check() ? route('dashboard') : route('welcome') }}"
                class="inline-flex text-sm font-medium text-emerald-400 underline-offset-2 hover:text-emerald-300 hover:underline"
            >
                ← {{ auth()->check() ? 'Volver al muro' : 'Inicio' }}
            </a>

            <noscript>
                <article class="mt-8 rounded-xl border border-slate-700/80 bg-slate-900/80 p-6">
                    <h1 class="text-2xl font-bold text-slate-50">{{ $post->title }}</h1>
                    <div class="mt-4 whitespace-pre-wrap text-slate-300">{{ strip_tags($post->description) }}</div>
                    <p class="mt-6 text-sm text-slate-500">Activa JavaScript para ver comentarios e interactuar.</p>
                </article>
            </noscript>

            <div id="post-show-card-mount" class="mt-8"></div>

            <section class="mt-12 border-t border-slate-800 pt-10" aria-labelledby="post-comments-heading">
                <h2 id="post-comments-heading" class="text-lg font-semibold text-slate-100">
                    Comentarios
                    <span id="post-show-heading-count" class="text-sm font-normal text-slate-500">
                        ({{ $postPayload['comments_count'] ?? 0 }})
                    </span>
                </h2>

                <div id="post-show-comments" class="mt-4 min-h-[4rem] rounded-xl border border-dashed border-slate-700/50 bg-slate-900/20 p-4 text-center text-sm text-slate-500">
                    Cargando comentarios…
                </div>

                @auth
                    <form id="wall-comment-form" class="mt-8 space-y-2 border-t border-slate-800 pt-8">
                        <label for="post-show-comment-body" class="block text-sm font-medium text-slate-300">
                            Tu comentario
                        </label>
                        <textarea
                            id="post-show-comment-body"
                            name="body"
                            rows="3"
                            required
                            maxlength="2000"
                            placeholder="Escribe algo…"
                            class="w-full rounded-xl border-slate-600 bg-slate-800/80 text-sm text-slate-100 shadow-sm placeholder:text-slate-500 focus:border-emerald-500 focus:ring-emerald-500"
                        ></textarea>
                        <button
                            type="submit"
                            class="inline-flex rounded-full bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-500"
                        >
                            Publicar
                        </button>
                    </form>
                @else
                    <p class="mt-8 text-sm text-slate-400">
                        <a href="{{ route('login') }}" class="font-medium text-emerald-400 underline hover:text-emerald-300">
                            Inicia sesión
                        </a>
                        para comentar.
                    </p>
                @endauth
            </section>
        </div>
    </div>
</x-app-layout>
