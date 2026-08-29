<x-app-layout
    :title="$pageTitle"
    :meta-description="$metaDescription"
    :og-image="$ogImage"
    :og-url="$ogUrl"
>
    <div id="post-show-page" class="w-full flex-1 bg-warm-50 text-ink">
        <textarea id="post-show-config" class="sr-only" readonly tabindex="-1" aria-hidden="true">@json($postShowConfig)</textarea>
        <textarea id="post-show-json" class="sr-only" readonly tabindex="-1" aria-hidden="true">@json($postPayload)</textarea>

        <div
            id="post-show-toast"
            class="pointer-events-none fixed top-20 left-1/2 z-[60] hidden max-w-md -translate-x-1/2 rounded-xl border border-emerald-500/40 bg-emerald-950/95 px-4 py-3 text-sm text-emerald-50 shadow-lg backdrop-blur-sm"
            role="status"
            aria-live="polite"
        ></div>

        <div class="mx-auto max-w-3xl px-4 py-8 sm:px-6">
            <a
                href="{{ auth()->check() ? route('dashboard') : route('welcome') }}"
                class="inline-flex items-center gap-1.5 text-sm font-medium text-fresh-600 underline-offset-2 hover:text-fresh-600 hover:underline"
            >
                <svg class="h-4 w-4 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
                </svg>
                <span>{{ auth()->check() ? 'Volver al muro' : 'Ir al inicio' }}</span>
            </a>

            <noscript>
                <article class="mt-8 rounded-xl border border-warm-200 bg-warm-0/90 p-6">
                    <h1 class="text-2xl font-bold text-slate-50">{{ $post->title }}</h1>
                    @if ($post->display_country !== null)
                        <div class="mt-3 inline-flex items-center gap-2 rounded-full border border-warm-200 bg-warm-100/70 px-3 py-1 text-xs text-ink-secondary">
                            @if (!empty($post->display_country['flag_url']))
                                <img src="{{ $post->display_country['flag_url'] }}" alt="" class="h-3.5 w-5 rounded-sm object-cover object-left" width="20" height="14" loading="lazy" decoding="async" />
                            @endif
                            <span>{{ $post->display_country['name'] }}</span>
                        </div>
                    @endif
                    <div class="mt-4 whitespace-pre-wrap text-ink-secondary">{{ strip_tags($post->description) }}</div>
                    <p class="mt-6 text-sm text-ink-muted">Activa JavaScript en tu navegador para ver comentarios y usar las interacciones de la publicación.</p>
                </article>
            </noscript>

            <div id="post-show-card-mount" class="mt-8"></div>

            <section class="mt-12 border-t border-warm-200 pt-10" aria-labelledby="post-comments-heading">
                <h2 id="post-comments-heading" class="text-lg font-semibold text-ink">
                    Comentarios
                    <span id="post-show-heading-count" class="text-sm font-normal text-ink-muted">
                        ({{ $postPayload['comments_count'] ?? 0 }})
                    </span>
                </h2>

                <div id="post-show-comments" class="mt-4 min-h-[4rem] rounded-xl border border-dashed border-warm-200/50 bg-warm-0/20 p-4 text-center text-sm text-ink-muted">
                    Cargando comentarios…
                </div>

                @auth
                    <form id="wall-comment-form" class="mt-8 space-y-2 border-t border-warm-200 pt-8">
                        <label for="post-show-comment-body" class="block text-sm font-medium text-ink-secondary">
                            Escribe un comentario
                        </label>
                        <textarea
                            id="post-show-comment-body"
                            name="body"
                            rows="3"
                            required
                            maxlength="2000"
                            placeholder="Comparte tu opinión o pregunta lo que quieras…"
                            class="w-full rounded-xl border-warm-200 bg-warm-100/80 text-sm text-ink shadow-sm placeholder:text-ink-muted focus:border-emerald-500 focus:ring-emerald-500"
                        ></textarea>
                        <button
                            type="submit"
                            class="inline-flex rounded-full bg-fresh-500 px-4 py-2 text-sm font-medium text-ink hover:bg-fresh-600"
                        >
                            Publicar
                        </button>
                    </form>
                @else
                    <p class="mt-8 text-sm text-ink-muted">
                        <a href="{{ route('login') }}" class="font-medium text-fresh-600 underline hover:text-fresh-600">
                            Inicia sesión
                        </a>
                        para unirte a la conversación.
                    </p>
                @endauth
            </section>
        </div>
    </div>
</x-app-layout>
