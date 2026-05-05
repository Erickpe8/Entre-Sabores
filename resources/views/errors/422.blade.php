@extends('errors.layout')

@section('title', 'Revisa los datos')

@section('content')
    @include('errors.partials.http-card', [
        'code' => '422',
        'badge' => 'Validación',
        'title' => 'La combinación necesita ajuste',
        'message' => 'Algunos datos no pasaron validación. Corrige el formulario y vuelve a enviar.',
        'severity' => 'soft',
    ])
@endsection

