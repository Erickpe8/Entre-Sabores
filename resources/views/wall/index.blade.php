<x-app-layout title="Entre Sabores — Muro de maridajes">
    <textarea id="wall-config" class="sr-only" readonly tabindex="-1" aria-hidden="true">@json($wallConfig)</textarea>

    <div id="wall-toast-root" class="fixed top-[calc(var(--navbar-height)+1rem)] left-1/2 z-[60] flex -translate-x-1/2 flex-col gap-2 pointer-events-none px-4 w-full max-w-md" aria-live="polite"></div>

    <div class="min-h-[100dvh] bg-page text-body">
        {{-- Filtros desktop: tabs + pills --}}
        @include('layouts.partials.wall-filter-bar')

        <div id="wall-skeleton" class="px-4 sm:px-6 py-8">
            <div
                class="grid gap-4 sm:gap-6 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-3 2xl:grid-cols-4"
                aria-hidden="true"
            >
                @foreach (range(1, 10) as $i)
                    <div class="bg-neutral-primary-soft flex w-full flex-col p-6 border border-default rounded-base shadow-xs animate-pulse">
                        <div class="flex items-center gap-3 pb-3">
                            <div class="h-11 w-11 shrink-0 rounded-full bg-neutral-secondary-medium"></div>
                            <div class="flex-1 space-y-2">
                                <div class="h-3.5 w-2/3 rounded-base bg-neutral-secondary-medium"></div>
                                <div class="h-3 w-1/3 rounded-base bg-neutral-tertiary-medium"></div>
                            </div>
                        </div>
                        <div class="post-card-media my-3 aspect-[4/3] w-full rounded-base bg-neutral-secondary-medium"></div>
                        <div class="mb-3 h-4 w-4/5 rounded-base bg-neutral-secondary-medium"></div>
                        <div class="mb-3 h-3 w-full rounded-base bg-neutral-tertiary-medium"></div>
                        <hr class="border-default border-t my-3" />
                        <div class="flex justify-between">
                            <div class="flex gap-5">
                                <div class="h-8 w-8 rounded-base bg-neutral-tertiary-medium"></div>
                                <div class="h-8 w-8 rounded-base bg-neutral-tertiary-medium"></div>
                            </div>
                            <div class="h-8 w-8 rounded-base bg-neutral-tertiary-medium"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="px-4 sm:px-6 pb-12 pt-2">
            <div
                id="posts-container"
                class="grid gap-4 sm:gap-6 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-3 2xl:grid-cols-4 min-h-[200px] opacity-100 transition-opacity duration-300 ease-out"
            ></div>
            <div id="feed-loading-more" class="hidden w-full flex justify-center py-6 items-center" aria-hidden="true">
                <span class="inline-block h-8 w-8 animate-spin rounded-full border-2 border-default border-t-accent-warm" aria-hidden="true"></span>
            </div>
            <div id="feed-scroll-anchor" class="h-8" aria-hidden="true"></div>
            <div id="feed-status" class="sr-only" aria-live="polite"></div>
        </div>
    </div>

    {{-- FAB tipo Flowbite / Twitter: un clic abre el modal (sin menú intermedio) --}}
    <div class="fixed end-6 bottom-24 sm:bottom-28 z-40">
        <button
            type="button"
            id="wall-fab-create-post"
            class="flex h-14 w-14 items-center justify-center rounded-full text-ink bg-fresh-500 shadow-lg shadow-emerald-900/40 hover:bg-fresh-600 focus:outline-none focus:ring-4 focus:ring-emerald-400/45 active:scale-95"
            aria-label="Nueva publicación"
        >
            <svg class="h-6 w-6" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.779 17.779 4.36 19.918 6.5 13.5m4.279 4.279 8.364-8.643a3.027 3.027 0 0 0-2.14-5.165 3.03 3.03 0 0 0-2.14.886L6.5 13.5m4.279 4.279L6.499 13.5m2.14 2.14 6.213-6.504M12.75 7.04 17 11.28"/>
            </svg>
        </button>
    </div>

    {{-- Modal crear publicación: compositor tipo Twitter / Instagram (sin formulario clásico) --}}
    <div id="create-post-modal" class="hidden fixed inset-0 z-50 flex items-end justify-center sm:items-center p-0 sm:p-5" role="dialog" aria-modal="true" aria-labelledby="create-post-title">
        <div id="create-post-backdrop" class="absolute inset-0 bg-black/80 backdrop-blur-md transition-opacity"></div>

        <div class="relative flex h-[100dvh] max-h-[100dvh] w-full max-w-3xl flex-col overflow-hidden rounded-none border-x-0 border-y border-white/[0.06] bg-zinc-950 shadow-[0_25px_80px_-12px_rgba(0,0,0,0.65)] sm:h-auto sm:max-h-[min(94dvh,920px)] sm:rounded-[1.75rem] sm:border sm:border-white/[0.08]">
            {{-- Panel etiquetas: búsqueda y selección --}}
            <div id="create-post-tag-panel" class="hidden absolute inset-0 z-[70] flex flex-col justify-end sm:justify-center sm:p-5" aria-hidden="true">
                <div id="create-post-tag-panel-dismiss" class="absolute inset-0 bg-black/55 backdrop-blur-[2px] pointer-events-auto"></div>
                <div class="pointer-events-none relative mx-auto flex w-full max-w-2xl flex-col justify-end sm:max-h-[min(84vh,760px)] sm:justify-center">
                    <div class="pointer-events-auto flex max-h-[min(88vh,820px)] flex-col overflow-hidden rounded-t-[1.35rem] border border-white/[0.08] bg-zinc-900/98 shadow-2xl ring-1 ring-white/[0.04] sm:rounded-2xl">
                        <div class="flex shrink-0 items-center justify-between gap-3 border-b border-white/[0.06] px-4 py-3">
                            <div>
                                <p class="text-base font-semibold text-ink">Etiquetas</p>
                            </div>
                            <button type="button" id="create-post-tag-panel-close" class="rounded-full p-2 text-zinc-400 transition hover:bg-warm-100 hover:text-ink" aria-label="Cerrar panel de etiquetas">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                        <div class="scrollbar-none min-h-0 flex-1 overflow-y-auto overscroll-contain px-4 pb-4 pt-3">
                            <p id="create-post-smart-hints-label" class="hidden mb-2 text-[11px] font-medium text-fresh-600/95">Etiquetas sugeridas según lo que escribes</p>
                            <div id="create-post-smart-hints" class="hidden mb-4 flex flex-wrap gap-2"></div>
                            <div id="create-post-selected-chips-bar" class="mb-4 flex min-h-[36px] flex-wrap gap-2"></div>
                            <label class="sr-only" for="create-post-tag-input">Buscar etiqueta</label>
                            <div class="relative">
                                <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-zinc-500" aria-hidden="true">
                                    <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
                                </span>
                                <input
                                    type="text"
                                    id="create-post-tag-input"
                                    autocomplete="off"
                                    spellcheck="false"
                                    placeholder="Buscar etiquetas..."
                                    class="create-post-tag-search-input w-full rounded-2xl border border-zinc-600/90 bg-zinc-950/80 py-3 pl-10 pr-4 text-sm text-zinc-100 placeholder:text-zinc-500 focus:border-emerald-500/55 focus:outline-none focus:ring-2 focus:ring-fresh-500/20"
                                />
                                <ul
                                    id="create-post-tag-dropdown"
                                    role="listbox"
                                    class="scrollbar-none hidden relative z-30 mt-2 max-h-72 overflow-y-auto rounded-xl border border-zinc-600/90 bg-zinc-950 py-1 shadow-2xl ring-1 ring-white/10"
                                ></ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Cabecera minimal (tipo Twitter) --}}
            <div class="grid shrink-0 grid-cols-[minmax(0,auto)_1fr_minmax(0,auto)] items-center gap-2 px-4 pb-2 pt-3 sm:px-6 sm:pt-5">
                <button type="button" id="create-post-close" class="rounded-full p-2 text-zinc-400 transition hover:bg-white/[0.06] hover:text-ink active:scale-95" aria-label="Cerrar">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
                <h2 id="create-post-title" class="truncate text-center text-base font-bold tracking-tight text-ink">Nueva publicación</h2>
                <span class="w-10 shrink-0" aria-hidden="true"></span>
            </div>

            <form id="create-post-form" class="flex min-h-0 flex-1 flex-col" enctype="multipart/form-data">
                <input type="hidden" name="title" id="create-post-field-title" value="" autocomplete="off" />
                <input type="hidden" name="description" id="create-post-field-description" value="" />
                <input type="hidden" name="composer_mode" id="create-post-composer-mode" value="create" />
                <input type="hidden" name="edit_post_id" id="create-post-edit-id" value="" />

                <div class="scrollbar-none min-h-0 flex-1 overflow-y-auto overscroll-y-contain px-4 pb-2 sm:px-7">
                    <div id="create-post-errors" class="mb-4 hidden rounded-2xl border border-red-500/30 bg-red-950/50 px-4 py-3 text-sm text-red-100"></div>

                    {{-- Vista previa en vivo: una sola superficie de composición --}}
                    <div id="create-post-editable-card" class="group/create-card overflow-hidden rounded-2xl border border-white/[0.07] bg-gradient-to-b from-zinc-900/95 to-zinc-950/98 shadow-lg shadow-ink/10 ring-1 ring-white/[0.04]">
                        <label class="sr-only" for="create-post-field-image">Imagen opcional</label>
                        <input id="create-post-field-image" name="image" type="file" accept="image/*" class="sr-only" tabindex="-1" />

                        <div
                            id="create-post-image-zone"
                            class="create-post-image-zone relative w-full overflow-hidden transition-[box-shadow] duration-200 ring-0 ring-emerald-500/0 data-[drag=active]:bg-emerald-500/[0.07] data-[drag=active]:ring-2 data-[drag=active]:ring-inset data-[drag=active]:ring-emerald-400/35"
                            data-drag="inactive"
                        >
                            <div id="create-post-image-empty" class="flex min-h-[200px] cursor-pointer flex-col items-center justify-center gap-2 bg-zinc-900/80 px-6 py-10 transition duration-200 hover:bg-zinc-800/50">
                                <span class="relative flex h-16 w-16 items-center justify-center rounded-2xl bg-zinc-800/90 shadow-inner ring-1 ring-white/[0.08]" aria-hidden="true">
                                    <svg class="h-9 w-9 text-zinc-200" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.622-.58 1.35-.58 2.111v6.5a2.25 2.25 0 002.25 2.25h9.5a2.25 2.25 0 002.25-2.25v-6.5c0-.76-.2-1.49-.58-2.11a2.31 2.31 0 00-1.64-1.055l-1.64-.31a2.25 2.25 0 00-1.86.64l-.6.6a1.5 1.5 0 01-1.06.44H8.9a1.5 1.5 0 01-1.06-.44l-.6-.6a2.25 2.25 0 00-1.86-.64l-1.64.31z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.25 10.5a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z" />
                                    </svg>
                                    <span class="pointer-events-none absolute left-1/2 top-[44%] h-2 w-2 -translate-x-1/2 -translate-y-1/2 rounded-full bg-violet-400 shadow-[0_0_0_2px_rgb(24_24_27/0.95)]" aria-hidden="true"></span>
                                </span>
                                <span class="text-base font-semibold text-zinc-100">Agregar imagen</span>
                                <span class="max-w-[240px] text-center text-sm leading-relaxed text-zinc-500">Arrastra o haz clic para subir</span>
                            </div>

                            <div id="create-post-image-filled" class="relative hidden aspect-[16/10] max-h-[min(38vh,320px)] w-full sm:aspect-[21/9] sm:max-h-[340px]">
                                <img id="create-post-image-preview" src="" alt="" class="h-full w-full object-cover" />
                                <div class="pointer-events-none absolute inset-0 bg-gradient-to-t from-zinc-950/75 via-transparent to-zinc-950/20"></div>
                                <button
                                    type="button"
                                    id="create-post-remove-image"
                                    class="absolute right-3 top-3 flex h-11 w-11 items-center justify-center rounded-full bg-black/55 text-ink shadow-lg backdrop-blur-md transition hover:scale-105 hover:bg-red-600/95"
                                    aria-label="Quitar imagen"
                                >
                                    <span class="sr-only">Quitar</span>
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </div>
                        </div>

                        <div class="flex flex-col gap-3 px-4 py-4 sm:gap-4 sm:px-6 sm:py-5">
                            <div
                                id="create-post-editable-title"
                                contenteditable="true"
                                role="textbox"
                                aria-multiline="false"
                                aria-label="Título"
                                data-placeholder="Añade un título claro a tu publicación"
                                class="create-post-ce create-post-ce--title ce-empty text-xl font-bold leading-snug tracking-tight text-ink outline-none sm:text-2xl focus-visible:ring-2 focus-visible:ring-fresh-500/35 focus-visible:ring-offset-2 focus-visible:ring-offset-zinc-950 rounded-sm"
                            ></div>

                            <div class="flex items-center gap-3">
                                @auth
                                    <img
                                        src="{{ auth()->user()->profile_photo_url }}"
                                        alt=""
                                        width="40"
                                        height="40"
                                        class="h-10 w-10 shrink-0 rounded-full border border-white/[0.08] object-cover ring-1 ring-white/[0.06]"
                                    />
                                    <div class="min-w-0 text-sm leading-tight">
                                        <span class="font-semibold text-zinc-100">{{ '@'.auth()->user()->username }}</span>
                                        <span class="text-zinc-600"> · </span>
                                        <span class="text-zinc-500">ahora</span>
                                    </div>
                                @else
                                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full border border-white/[0.08] bg-zinc-800 text-zinc-500" aria-hidden="true">
                                        <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
                                    </div>
                                    <div class="text-sm text-zinc-500"><span class="font-medium text-zinc-400">invitado</span> · ahora</div>
                                @endauth
                            </div>

                            <div
                                id="create-post-editable-description"
                                contenteditable="true"
                                role="textbox"
                                aria-multiline="true"
                                aria-label="Descripción"
                                data-placeholder="Describe el plato, la bebida o la experiencia que quieres compartir"
                                class="create-post-ce create-post-ce--body ce-empty min-h-[6.5rem] text-[15px] leading-relaxed text-zinc-300 outline-none focus-visible:ring-2 focus-visible:ring-fresh-500/35 focus-visible:ring-offset-2 focus-visible:ring-offset-zinc-950 rounded-xl"
                            ></div>

                            <div id="create-post-card-tag-pills" class="flex min-h-[28px] flex-wrap gap-x-2 gap-y-1.5 text-sm"></div>

                            <button
                                type="button"
                                id="create-post-open-tags"
                                class="inline-flex w-fit items-center gap-2 rounded-full border border-zinc-600/80 bg-zinc-800/40 px-4 py-2 text-sm font-medium text-fresh-600/95 ring-1 ring-white/[0.04] transition hover:border-emerald-500/40 hover:bg-fresh-600/10 hover:text-emerald-200 active:scale-[0.98]"
                            >
                                <svg class="h-4 w-4 text-fresh-600/90" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                                </svg>
                                Agregar etiquetas
                            </button>

                            <div class="pointer-events-none flex items-center gap-5 border-t border-white/[0.06] pt-4 select-none text-zinc-500">
                                <span class="inline-flex items-center gap-1.5 text-sm font-medium tabular-nums">
                                    <svg class="h-4 w-4 text-zinc-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                                    </svg>
                                    <span>0</span>
                                </span>
                                <span class="inline-flex items-center gap-1.5 text-sm font-medium tabular-nums">
                                    <svg class="h-4 w-4 text-zinc-500 opacity-85" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                                    </svg>
                                    <span>0</span>
                                </span>
                            </div>
                        </div>
                    </div>

                    <p id="create-post-validation-hint" class="mt-3 text-center text-[11px] text-amber-400/90"></p>
                </div>

                <div class="safe-bottom shrink-0 border-t border-white/[0.07] bg-zinc-950/95 px-4 py-4 backdrop-blur-xl sm:rounded-b-[1.75rem] sm:px-7">
                    <div class="flex flex-col-reverse gap-3 sm:flex-row sm:items-center sm:justify-end">
                        <button type="button" id="create-post-cancel" class="inline-flex min-h-[48px] w-full items-center justify-center rounded-full border border-zinc-600/90 bg-transparent px-6 text-sm font-medium text-zinc-300 transition hover:bg-zinc-800/80 sm:w-auto">
                            Cancelar
                        </button>
                        <button type="submit" id="create-post-submit" class="inline-flex min-h-[48px] w-full items-center justify-center rounded-full bg-gradient-to-r from-emerald-500 to-teal-600 px-8 text-sm font-bold text-ink shadow-lg shadow-emerald-950/40 transition hover:brightness-110 active:scale-[0.98] disabled:pointer-events-none disabled:opacity-45 sm:w-auto">
                            Publicar
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal detalle publicación --}}
    <div id="wall-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-0 sm:p-4" role="dialog" aria-modal="true" aria-label="Detalle de publicación">
        <div id="wall-modal-backdrop" class="absolute inset-0 bg-black/70 backdrop-blur-sm"></div>
        <div
            id="wall-modal-panel"
            class="scrollbar-none relative w-full h-full max-h-[100dvh] sm:h-auto sm:max-h-[80vh] sm:max-w-2xl overflow-y-auto overflow-x-hidden overscroll-y-contain sm:rounded-2xl rounded-none bg-warm-0 shadow-2xl shadow-black/50 border-0 sm:border border-warm-200"
        >
            <div class="sticky top-0 flex justify-end bg-warm-0/95 border-b border-warm-200 px-4 py-2 rounded-t-2xl z-10 backdrop-blur-sm">
                <button
                    type="button"
                    id="wall-modal-close"
                    class="rounded-full min-h-[44px] min-w-[44px] p-2 text-ink-muted hover:bg-warm-100 hover:text-ink active:scale-95"
                    aria-label="Cerrar"
                >
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div id="wall-modal-body" class="px-4 sm:px-6 pb-8 pt-2"></div>
        </div>
    </div>
</x-app-layout>
