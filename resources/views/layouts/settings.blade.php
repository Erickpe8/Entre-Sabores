@php
    /** @var \App\Models\User $user */
    $settingsTitle = $title ?? __('Configuración');
    $activeTab = $active ?? 'profile';
    $avatarId = $avatarId ?? null;
@endphp

<x-app-layout title="{{ $settingsTitle }} — {{ config('app.name') }}">
    <div class="min-h-[100dvh] bg-gradient-to-br from-[#020617] via-[#0f172a] to-[#022c22] pb-16">
        <div class="max-w-7xl mx-auto px-6 py-10 grid lg:grid-cols-3 gap-8">
            <aside class="space-y-6 lg:self-start lg:sticky lg:top-24">
                <div class="bg-white/5 backdrop-blur-xl rounded-2xl p-6 shadow-xl border border-warm-200">
                    <x-profile-card
                        :user="$user"
                        :avatar-id="$avatarId"
                        :show-public-link="true"
                        :show-country="true"
                        :show-age="true"
                        :show-member-since="true"
                        :show-preferences="true"
                        :show-social-links="true"
                    />

                    @hasSection('settings-sidebar-actions')
                        <div class="mt-4 border-t border-warm-200 pt-4">
                            @yield('settings-sidebar-actions')
                        </div>
                    @endif
                </div>
            </aside>

            <main class="lg:col-span-2 space-y-6">
                @include('settings.partials.settings-nav', ['active' => $activeTab])
                @yield('settings-content')
            </main>
        </div>
    </div>

    @yield('settings-footer')
</x-app-layout>
