@extends('errors.layout')

@section('title', 'Contenido demasiado pesado')

@section('content')
    @include('errors.partials.http-card', [
        'code' => '413',
        'badge' => 'Carga',
        'title' => 'Ese archivo superó el tamaño permitido',
        'message' => 'Reduce el peso de la imagen o contenido y vuelve a intentarlo.',
        'severity' => 'soft',
    ])
@endsection

