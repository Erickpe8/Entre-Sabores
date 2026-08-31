<div
    id="onboarding-modal"
    class="onboarding-modal hidden fixed inset-0 z-[65] flex items-center justify-center p-4"
    role="dialog"
    aria-modal="true"
    aria-labelledby="onboarding-modal-title"
    aria-hidden="true"
>
    <div id="onboarding-modal-backdrop" class="absolute inset-0 bg-black/60 backdrop-blur-sm" aria-hidden="true"></div>

    <div class="onboarding-modal__panel relative z-10 w-full max-w-lg overflow-hidden rounded-2xl border border-default bg-neutral-primary-soft shadow-2xl">
        <div class="flex items-center justify-between border-b border-default px-5 py-3">
            <div class="flex items-center gap-2" id="onboarding-modal-dots" aria-hidden="true"></div>
            <button
                type="button"
                id="onboarding-modal-skip"
                class="text-xs font-medium text-muted transition hover:text-heading"
            >
                Omitir
            </button>
        </div>

        <div class="px-5 py-6 text-center">
            <div id="onboarding-modal-art" class="onboarding-modal__art mx-auto mb-5 max-w-sm"></div>
            <h2 id="onboarding-modal-title" class="text-xl font-bold text-heading"></h2>
            <p id="onboarding-modal-message" class="mt-2 text-sm leading-relaxed text-body"></p>
        </div>

        <div class="flex items-center justify-between gap-3 border-t border-default px-5 py-4">
            <button
                type="button"
                id="onboarding-modal-prev"
                class="btn btn-secondary px-4 py-2 text-sm hidden"
            >
                Anterior
            </button>
            <button
                type="button"
                id="onboarding-modal-next"
                class="btn btn-primary ms-auto px-5 py-2 text-sm"
            >
                Siguiente
            </button>
        </div>
    </div>
</div>
