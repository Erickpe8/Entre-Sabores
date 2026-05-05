@extends('errors.layout')

@section('title', 'Recurso ya no disponible')

@section('content')
    @include('errors.partials.http-card', [
        'code' => '410',
        'badge' => 'Retirado',
        'title' => 'Este contenido salió del menú',
        'message' => 'La publicación o recurso fue retirado y ya no está disponible.',
        'severity' => 'soft',
    ])
@endsection

