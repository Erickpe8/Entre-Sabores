<div
    id="{{ $modalId }}"
    class="avatar-cropper-modal fixed inset-0 z-[60] hidden items-center justify-center bg-[rgba(2,48,71,0.55)] px-3 py-3 backdrop-blur-sm sm:px-4 sm:py-6"
    style="padding-top: max(env(safe-area-inset-top), 0.75rem); padding-bottom: max(env(safe-area-inset-bottom), 0.75rem);"
    role="dialog"
    aria-modal="true"
    aria-labelledby="{{ $modalId }}-title"
>
    <div class="avatar-cropper-modal__dialog">
        <header class="avatar-cropper-modal__header">
            <div>
                <h2 id="{{ $modalId }}-title" class="avatar-cropper-modal__title">Ajusta tu foto</h2>
                <p class="avatar-cropper-modal__subtitle">Arrastra y haz zoom para encuadrar. Se verá en un círculo.</p>
            </div>
        </header>

        <div class="avatar-cropper-modal__stage">
            <img id="{{ $cropImageId }}" alt="Editor de avatar" class="avatar-cropper-modal__image max-w-full">
        </div>

        <footer class="avatar-cropper-modal__footer">
            <button id="{{ $modalId }}_btnReset" type="button" class="btn btn-secondary min-h-[44px] px-4 py-2">
                Reintentar
            </button>

            <div class="avatar-cropper-modal__footer-primary">
                <button
                    id="{{ $modalId }}_btnCancel"
                    type="button"
                    class="btn min-h-[44px] border border-danger bg-transparent px-4 py-2 text-danger hover:bg-danger/10"
                >
                    Cancelar
                </button>

                <button id="{{ $modalId }}_btnApply" type="button" class="btn btn-primary min-h-[44px] px-5 py-2">
                    Aplicar
                </button>
            </div>
        </footer>
    </div>
</div>

@php
    $cropperClientConfig = [
        'mode' => $mode,
        'previewId' => $previewId,
        'base64InputId' => $base64InputId,
        'openButtonId' => $openButtonId,
        'cropImageId' => $cropImageId,
        'modalId' => $modalId,
        'cropSource' => $cropSource,
        'cropSourceInputId' => $cropSourceInputId,
        'dataTransferInputId' => $dataTransferInputId,
        'formId' => $formId,
        'stepOneId' => $stepOneId,
        'stepTwoId' => $stepTwoId,
        'stepIds' => $stepIds,
    ];
@endphp

<textarea class="sr-only" data-avatar-cropper-config readonly tabindex="-1" aria-hidden="true">@json($cropperClientConfig)</textarea>
