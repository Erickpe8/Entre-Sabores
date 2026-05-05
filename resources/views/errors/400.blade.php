@extends('errors.layout')

@section('title', 'Solicitud inválida')

@section('content')
    @include('errors.partials.http-card', [
        'code' => '400',
        'badge' => 'Error leve',
        'title' => 'Esta combinación llegó incompleta',
        'message' => 'Recibimos datos fuera de la receta esperada. Revisa el formulario y vuelve a intentar.',
        'severity' => 'soft',
    ])
@endsection

