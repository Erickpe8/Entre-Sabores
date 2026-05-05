@extends('errors.layout')

@section('title', 'Necesitamos un poco más de tiempo')

@section('meta_description', 'La respuesta tardó más de lo esperado. Inténtalo de nuevo en unos segundos.')

@section('content')
    @include('errors.partials.http-card', [
        'code' => '504',
        'badge' => 'Servicio',
        'title' => 'La respuesta tardó más de lo habitual',
        'message' => 'Estamos reintentando la conexión con nuestros servicios. Intenta de nuevo en unos segundos.',
        'severity' => 'critical',
    ])
@endsection

