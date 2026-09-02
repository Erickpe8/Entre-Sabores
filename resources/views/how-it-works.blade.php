@extends('layouts.marketing', ['active' => 'how-it-works'])

@section('title', 'Cómo funciona — Entre Sabores')

@section('meta_description', 'Cómo publicar un maridaje, cómo funciona el análisis asistido y cómo ver el resultado en tu publicación.')

@section('canonical', route('how-it-works'))

@section('content')
    <article class="mx-auto max-w-3xl space-y-10">
        <header class="space-y-4 text-center sm:text-left">
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-accent-cool">Cómo funciona</p>
            <h1 class="text-3xl font-extrabold tracking-tight text-heading sm:text-4xl md:text-5xl">
                Maridajes con contexto y análisis en la misma tarjeta
            </h1>
            <p class="text-lg leading-relaxed text-body">
                Entre Sabores une una publicación tipo red social con un análisis técnico breve generado en segundo plano: tú escribes la experiencia; el sistema la complementa con una lectura estructurada cuando la configuración del servidor lo permite.
            </p>
        </header>

        <ol class="how-steps space-y-8">
            <li class="how-steps__item">
                <span class="how-steps__marker" aria-hidden="true"></span>
                <div class="how-steps__body">
                    <h2 class="text-xl font-bold text-heading">Publicas tu maridaje</h2>
                    <p class="mt-2 text-body">
                        Añades título, descripción y etiquetas (país, tipo de comida, bebida, experiencia). Opcionalmente una imagen. Las etiquetas ayudan a clasificar; el relato en texto es la base del análisis automático.
                    </p>
                </div>
            </li>
            <li class="how-steps__item">
                <span class="how-steps__marker" aria-hidden="true"></span>
                <div class="how-steps__body">
                    <h2 class="text-xl font-bold text-heading">El servidor encola un análisis</h2>
                    <p class="mt-2 text-body">
                        No hace falta esperar en la misma pantalla: un proceso en cola llama a un modelo de lenguaje configurado en el entorno del servidor. Las credenciales de la API no salen del backend.
                    </p>
                </div>
            </li>
            <li class="how-steps__item">
                <span class="how-steps__marker" aria-hidden="true"></span>
                <div class="how-steps__body">
                    <h2 class="text-xl font-bold text-heading">Ves el resultado en la tarjeta</h2>
                    <p class="mt-2 text-body">
                        En el muro y en el detalle del post, la tarjeta permite alternar entre la publicación y el panel de análisis (historia, afinidad, equilibrio, recomendación y puntuación). Si el análisis tarda unos segundos, la interfaz puede actualizarse cuando está listo sin recargar la página.
                    </p>
                </div>
            </li>
            <li class="how-steps__item">
                <span class="how-steps__marker" aria-hidden="true"></span>
                <div class="how-steps__body">
                    <h2 class="text-xl font-bold text-heading">Autor: volver a analizar</h2>
                    <p class="mt-2 text-body">
                        Si editaste el texto o quieres reintentar, como autor puedes solicitar un nuevo análisis desde la vista del post cuando la aplicación lo ofrezca.
                    </p>
                </div>
            </li>
        </ol>

        <div class="rounded-base border border-accent-gold-soft bg-accent-gold-soft px-5 py-4 text-sm leading-relaxed text-heading">
            <strong class="font-semibold text-heading">Nota:</strong>
            <span class="text-body"> si no hay clave de API o el servicio no responde, el sistema puede guardar un mensaje de respaldo para que la tarjeta no quede vacía. La experiencia principal no depende de volver a llamar a la IA desde el navegador.</span>
        </div>

        <div class="flex flex-col items-center justify-center gap-4 pt-2 sm:flex-row sm:justify-between">
            <a
                href="{{ route('explore') }}"
                class="text-sm font-semibold text-accent-cool underline-offset-2 hover:underline"
            >← Volver a Explorar</a>
            @guest
                <a href="{{ route('register') }}" class="btn btn-primary px-6 py-2.5 text-sm">
                    Empezar
                </a>
            @endguest
        </div>
    </article>
@endsection
