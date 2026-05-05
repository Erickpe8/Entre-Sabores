@extends('errors.layout')

@section('title', 'Tiempo de espera agotado')

@section('content')
    @include('errors.partials.http-card', [
        'code' => '408',
        'badge' => 'Conexión',
        'title' => 'La cocción tardó más de lo esperado',
        'message' => 'La solicitud se quedó sin tiempo. Reintenta y retomamos donde quedamos.',
        'severity' => 'soft',
    ])
@endsection

