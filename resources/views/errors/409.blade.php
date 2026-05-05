@extends('errors.layout')

@section('title', 'Conflicto al guardar')

@section('content')
    @include('errors.partials.http-card', [
        'code' => '409',
        'badge' => 'Conflicto',
        'title' => 'Dos manos ajustaron la receta al mismo tiempo',
        'message' => 'Detectamos un cambio simultáneo. Actualiza y vuelve a guardar tu contenido.',
        'severity' => 'soft',
    ])
@endsection

