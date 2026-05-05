@extends('errors.layout')

@section('title', 'Método no permitido')

@section('content')
    @include('errors.partials.http-card', [
        'code' => '405',
        'badge' => 'Operación',
        'title' => 'Esa acción no encaja con este paso',
        'message' => 'Intentaste una acción no permitida para esta ruta. Vuelve y prueba el flujo recomendado.',
        'severity' => 'soft',
    ])
@endsection

