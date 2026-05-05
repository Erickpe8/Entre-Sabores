@extends('errors.layout')

@section('title', 'La conexión falló por un instante')

@section('meta_description', 'Un servicio externo no respondió a tiempo. Puedes reintentar en breve.')

@section('content')
    @include('errors.partials.http-card', [
        'code' => '502',
        'badge' => 'Integración',
        'title' => 'Un proveedor externo no respondió bien',
        'message' => 'Estamos reconectando servicios para servirte la mejor experiencia.',
        'severity' => 'critical',
    ])
@endsection

