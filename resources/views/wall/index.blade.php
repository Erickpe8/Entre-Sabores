<x-app-layout title="Entre Sabores — Muro de maridajes">
    <script type="application/json" id="wall-config">@json($wallConfig)</script>

    <div class="min-h-screen bg-slate-950 text-slate-100">
        {{-- Filtros: misma fila, scroll horizontal, estética daily.dev / oscuro --}}
        <div class="sticky top-16 z-30 border-b border-slate-800/90 bg-slate-900/95 shadow-sm shadow-black/20 backdrop-blur-md">
            <div class="overflow-x-auto overscroll-x-contain wall-scroll-x">
                <div
                    class="flex flex-nowrap items-center gap-x-2 py-2.5 px-4 sm:px-6 min-w-max text-sm"
                    id="wall-filter-bar"
                >
                    @foreach ($countries as $country)
                        <button
                            type="button"
                            data-country-chip="{{ $country->id }}"
                            class="wall-chip shrink-0 rounded-full px-3 py-1.5 text-xs font-medium transition bg-slate-800/90 text-slate-300 hover:bg-slate-700 hover:text-white inline-flex items-center gap-1"
                        >
                            <span>{{ $country->flag_emoji }}</span>
                            <span>{{ $country->name }}</span>
                        </button>
                    @endforeach

                    <span class="mx-2 h-5 w-px shrink-0 bg-slate-600" aria-hidden="true"></span>

                    @foreach (['tradicional' => 'Tradicional', 'callejero' => 'Callejero', 'gourmet' => 'Gourmet', 'dulce' => 'Dulce', 'salado' => 'Salado'] as $val => $label)
                        <button
                            type="button"
                            data-adv-experience="{{ $val }}"
                            class="wall-chip shrink-0 rounded-full px-3 py-1.5 text-xs font-medium transition bg-slate-800/90 text-slate-300 hover:bg-slate-700 hover:text-white"
                        >
                            {{ $label }}
                        </button>
                    @endforeach

                    <span class="mx-2 h-5 w-px shrink-0 bg-slate-600" aria-hidden="true"></span>

                    @foreach (['cafe' => 'Café', 'vino' => 'Vino', 'cerveza' => 'Cerveza', 'tradicional' => 'Bebidas tradicionales'] as $val => $label)
                        <button
                            type="button"
                            data-adv-drink="{{ $val }}"
                            class="wall-chip shrink-0 rounded-full px-3 py-1.5 text-xs font-medium transition bg-slate-800/90 text-slate-300 hover:bg-slate-700 hover:text-white"
                        >
                            {{ $label }}
                        </button>
                    @endforeach

                    <span class="mx-2 h-5 w-px shrink-0 bg-slate-600" aria-hidden="true"></span>

                    <button
                        type="button"
                        data-sort="recent"
                        class="wall-chip wall-chip-sort shrink-0 rounded-full px-3 py-1.5 text-xs font-medium transition bg-emerald-500/15 text-emerald-300 ring-1 ring-emerald-500/35 hover:bg-emerald-500/25"
                    >
                        Más recientes
                    </button>
                    <button
                        type="button"
                        data-sort="popular"
                        class="wall-chip wall-chip-sort shrink-0 rounded-full px-3 py-1.5 text-xs font-medium transition bg-slate-800/90 text-slate-300 hover:bg-slate-700 hover:text-white"
                    >
                        Más populares
                    </button>
                </div>
            </div>
        </div>

        {{-- Skeleton alineado al grid --}}
        <div id="wall-skeleton" class="px-4 sm:px-6 py-8">
            <div
                class="grid gap-6 grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5"
                aria-hidden="true"
            >
                @foreach (range(1, 10) as $i)
                    <div class="overflow-hidden rounded-xl border border-slate-700/80 bg-slate-900/50 shadow-sm animate-pulse">
                        <div class="h-[140px] bg-slate-800"></div>
                        <div class="space-y-3 p-4">
                            <div class="h-4 bg-slate-700/80 rounded w-5/6"></div>
                            <div class="h-3 bg-slate-800 rounded w-full"></div>
                            <div class="h-3 bg-slate-800 rounded w-11/12"></div>
                            <div class="h-3 bg-slate-800 rounded w-2/3"></div>
                            <div class="flex justify-between border-t border-slate-700/80 pt-3 mt-3">
                                <div class="h-5 bg-slate-700 rounded-full w-24"></div>
                                <div class="h-4 bg-slate-800 rounded w-10"></div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Feed: grid responsive, densidad alta, sin columnas por país --}}
        <div class="px-4 sm:px-6 pb-12 pt-2">
            <div
                id="posts-container"
                class="grid gap-6 grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 min-h-[200px]"
            ></div>
        </div>
    </div>

    {{-- Modal detalle (tema oscuro, mismo lenguaje visual) --}}
    <div id="wall-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4">
        <div id="wall-modal-backdrop" class="absolute inset-0 bg-black/70 backdrop-blur-sm"></div>
        <div
            id="wall-modal-panel"
            class="relative max-w-2xl w-full max-h-[90vh] overflow-y-auto rounded-2xl bg-slate-900 shadow-2xl shadow-black/50 border border-slate-700"
        >
            <div class="sticky top-0 flex justify-end bg-slate-900/95 border-b border-slate-700 px-4 py-2 rounded-t-2xl z-10 backdrop-blur-sm">
                <button
                    type="button"
                    id="wall-modal-close"
                    class="rounded-full p-2 text-slate-400 hover:bg-slate-800 hover:text-slate-200"
                    aria-label="Cerrar"
                >
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div id="wall-modal-body" class="px-6 pb-8 pt-2"></div>
        </div>
    </div>
</x-app-layout>
