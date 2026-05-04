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
