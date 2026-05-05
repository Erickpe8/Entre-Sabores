@extends('errors.layout')

@section('title', 'Este contenido no está disponible por ahora')

@section('meta_description', 'No tienes permiso para ver esta sección. Te indicamos los siguientes pasos.')

@section('content')
    @include('errors.partials.http-card', [
        'code' => '403',
        'badge' => 'Acceso',
        'title' => 'Esta mesa tiene reserva',
        'message' => 'No puedes entrar a esta parte del sitio con tu cuenta actual. Si crees que deberías poder verla, prueba otra ruta o vuelve a iniciar sesión.',
        'severity' => 'soft',
    ])
@endsection
