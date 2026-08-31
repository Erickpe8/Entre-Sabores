<div
    id="celebration-modal"
    class="celebration-modal hidden fixed inset-0 z-[66] flex items-center justify-center p-4"
    role="dialog"
    aria-modal="true"
    aria-labelledby="celebration-modal-title"
    aria-hidden="true"
>
    <div id="celebration-modal-backdrop" class="absolute inset-0 bg-black/60 backdrop-blur-sm" aria-hidden="true"></div>

    <div class="celebration-modal__panel relative z-10 w-full max-w-md overflow-hidden rounded-2xl border border-default bg-neutral-primary-soft p-6 text-center shadow-2xl">
        <div id="celebration-modal-art" class="celebration-modal__art mx-auto mb-4 max-w-xs"></div>
        <h2 id="celebration-modal-title" class="text-xl font-bold text-heading">¡Tu primer maridaje!</h2>
        <p id="celebration-modal-message" class="mt-2 text-sm leading-relaxed text-body">
            Acabas de publicar en Entre Sabores. La comunidad ya puede descubrir tu experiencia.
        </p>
        <button type="button" id="celebration-modal-close" class="btn btn-primary mt-6 w-full sm:w-auto px-6 py-2.5 text-sm">
            Continuar
        </button>
    </div>
</div>
