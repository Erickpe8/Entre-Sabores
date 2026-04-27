<x-app-layout title="{{ '@'.$user->username }} — {{ config('app.name') }}">
    <div class="min-h-screen bg-gradient-to-br from-[#020617] via-[#0f172a] to-[#022c22] pb-16">
        <div class="max-w-7xl mx-auto px-6 py-10 grid lg:grid-cols-3 gap-8">
            <aside class="space-y-6 lg:self-start lg:sticky lg:top-24">
                <div class="bg-white/5 backdrop-blur-xl rounded-2xl p-6 shadow-xl border border-white/10">
                    <div class="flex flex-col items-center text-center space-y-3">
                        <img
                            src="{{ $user->profile_photo_url }}"
                            alt=""
                            class="w-28 h-28 rounded-full border-2 border-green-400 object-cover shrink-0"
                        >

                        <div class="space-y-1">
                            <h1 class="text-white font-semibold text-xl leading-tight">
                                {{ $user->first_name }} {{ $user->last_name }}
                            </h1>

                            <p class="text-gray-400 text-sm">
                                {{ '@'.$user->username }}
                            </p>

                            <div class="flex items-center justify-center gap-2 text-gray-400 text-sm">
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
                </div>
            </aside>

            <main class="lg:col-span-2 space-y-6">
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
            </main>
        </div>
    </div>
</x-app-layout>
