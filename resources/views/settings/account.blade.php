@extends('layouts.settings', [
    'title' => __('Cuenta y seguridad'),
    'user' => $user,
    'active' => 'account',
])

@section('settings-content')
    <div class="bg-white/5 backdrop-blur rounded-2xl p-6 border border-white/10 hover:scale-[1.01] transition shadow-lg shadow-black/20">
        @include('profile.partials.update-password-form')
    </div>

    <div class="bg-white/5 backdrop-blur rounded-2xl p-6 border border-white/10 hover:scale-[1.01] transition shadow-lg shadow-black/20">
        @include('profile.partials.delete-user-form')
    </div>
@endsection
@extends('layouts.settings', [
    'title' => __('Cuenta y seguridad'),
    'user' => $user,
    'active' => 'account',
])

@section('settings-content')
    <div class="bg-white/5 backdrop-blur rounded-2xl p-6 border border-white/10 hover:scale-[1.01] transition shadow-lg shadow-black/20">
        @include('profile.partials.update-password-form')
    </div>

    <div class="bg-white/5 backdrop-blur rounded-2xl p-6 border border-white/10 hover:scale-[1.01] transition shadow-lg shadow-black/20">
        @include('profile.partials.delete-user-form')
    </div>
@endsection
<x-app-layout title="{{ __('Cuenta y seguridad') }} — {{ config('app.name') }}">
    <div class="min-h-[100dvh] bg-gradient-to-br from-[#020617] via-[#0f172a] to-[#022c22] pb-16">
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
                            <p class="text-white font-semibold leading-tight">
                                {{ $user->first_name }} {{ $user->last_name }}
                            </p>
                            <p class="text-gray-400 text-sm">{{ '@'.$user->username }}</p>
                        </div>
                        <a
                            href="{{ route('profile.show', $user->username) }}"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="inline-flex items-center gap-1.5 text-xs text-green-400 hover:text-green-300 hover:underline"
                        >
                            <i data-lucide="external-link" class="w-3.5 h-3.5 shrink-0" aria-hidden="true"></i>
                            Ver perfil público
                        </a>
                        <a
                            href="{{ route('settings.profile') }}"
                            class="mt-2 inline-flex text-xs text-slate-400 hover:text-white underline-offset-2 hover:underline"
                        >
                            ← Editar datos del perfil
                        </a>
                    </div>
                </div>
            </aside>

            <main class="lg:col-span-2 space-y-6">
                @include('settings.partials.settings-nav', ['active' => 'account'])

                <div class="bg-white/5 backdrop-blur rounded-2xl p-6 border border-white/10 hover:scale-[1.01] transition shadow-lg shadow-black/20">
                    @include('profile.partials.update-password-form')
                </div>

                <div class="bg-white/5 backdrop-blur rounded-2xl p-6 border border-white/10 hover:scale-[1.01] transition shadow-lg shadow-black/20">
                    @include('profile.partials.delete-user-form')
                </div>
            </main>
        </div>
    </div>
</x-app-layout>
