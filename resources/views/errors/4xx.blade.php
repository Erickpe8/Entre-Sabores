@extends('errors.layout')

@php($status = (int) ($exception->getStatusCode() ?? 400))

@section('title', 'Este contenido no está disponible por ahora')

@section('meta_description', 'No pudimos completar esta acción. Te sugerimos los siguientes pasos.')

@section('content')
    @include('errors.partials.http-card', [
        'code' => (string) $status,
        'badge' => 'Solicitud',
        'title' => 'Algo en esta solicitud necesita ajuste',
        'message' => 'No pudimos completar esta acción tal como llegó. Comprueba los datos e inténtalo de nuevo, o vuelve al inicio.',
        'severity' => 'soft',
    ])
@endsection
