@extends('errors.layout')

@section('title', 'Estamos ajustando algunos detalles')

@section('meta_description', 'Hubo un inconveniente temporal. Puedes reintentar en unos momentos.')

@section('content')
    @include('errors.partials.http-card', [
        'code' => '500',
        'badge' => 'Servicio',
        'title' => 'Esta combinación no salió como esperábamos',
        'message' => 'Algo falló en nuestra cocina digital y ya lo estamos corrigiendo. Vuelve a intentar dentro de un momento.',
        'severity' => 'critical',
    ])
@endsection
