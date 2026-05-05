@extends('errors.layout')

@php($status = (int) ($exception->getStatusCode() ?? 500))

@section('title', 'Estamos ajustando algunos detalles')

@section('meta_description', 'El servicio tuvo un inconveniente temporal. Puedes reintentar en breve.')

@section('content')
    @include('errors.partials.http-card', [
        'code' => (string) $status,
        'badge' => 'Servicio',
        'title' => 'Estamos reajustando la receta',
        'message' => 'Tuvimos un inconveniente interno. El equipo ya está en ello; puedes reintentar en unos minutos.',
        'severity' => 'critical',
    ])
@endsection
