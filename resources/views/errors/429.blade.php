@extends('errors.layout')

@section('title', 'Demasiadas solicitudes')

@section('content')
    @include('errors.partials.http-card', [
        'code' => '429',
        'badge' => 'Límite',
        'title' => 'Vamos con calma para cuidar la cocina',
        'message' => 'Recibimos muchas acciones seguidas. Espera unos segundos e inténtalo de nuevo.',
        'severity' => 'soft',
    ])
@endsection

