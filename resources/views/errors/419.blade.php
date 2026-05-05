@extends('errors.layout')

@section('title', 'Renueva tu sesión para continuar')

@section('meta_description', 'Tu sesión expiró por seguridad. Reintenta la acción o vuelve al inicio.')

@section('content')
    @include('errors.partials.http-card', [
        'code' => '419',
        'badge' => 'Sesión',
        'title' => 'Tu sesión pidió un pequeño refresco',
        'message' => 'Por seguridad, esta página ya no acepta la acción anterior. Recarga o vuelve a intentar lo que estabas haciendo.',
        'severity' => 'soft',
    ])
@endsection
