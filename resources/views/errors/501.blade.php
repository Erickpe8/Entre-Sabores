@extends('errors.layout')

@section('title', 'Función no disponible')

@section('content')
    @include('errors.partials.http-card', [
        'code' => '501',
        'badge' => 'Servidor',
        'title' => 'Ese paso aún no está en el menú',
        'message' => 'La funcionalidad que intentas usar todavía no está disponible en este entorno.',
        'severity' => 'critical',
    ])
@endsection

