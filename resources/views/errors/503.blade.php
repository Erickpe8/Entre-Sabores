@extends('errors.layout')

@section('title', 'Volvemos en un momento')

@section('meta_description', 'Estamos en una pausa breve. Gracias por tu paciencia.')

@section('content')
    @include('errors.partials.http-card', [
        'code' => '503',
        'badge' => 'Mantenimiento',
        'title' => 'Estamos afinando la cocina',
        'message' => 'En breve volveremos a servir contenido con normalidad. Gracias por tu paciencia.',
        'severity' => 'critical',
    ])
@endsection

