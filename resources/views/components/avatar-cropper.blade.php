<style>
    .custom-scroll {
        overflow-y: auto;
        scrollbar-width: none;
        -ms-overflow-style: none;
        scroll-behavior: smooth;
    }

    .custom-scroll::-webkit-scrollbar {
        display: none;
    }

    /* Refuerzo local (v1) para asegurar máscara circular visible */
    .avatar-cropper-modal .cropper-crop-box,
    .avatar-cropper-modal .cropper-view-box,
    .avatar-cropper-modal .cropper-face {
        border-radius: 50% !important;
    }

    .avatar-cropper-modal .cropper-view-box {
        outline: 2px solid #22c55e !important;
        box-shadow: 0 0 0 9999px rgba(0, 0, 0, 0.55) !important;
    }

    .avatar-cropper-modal .cropper-face {
        background-color: transparent !important;
    }

    .avatar-cropper-modal .cropper-dashed,
    .avatar-cropper-modal .cropper-center,
    .avatar-cropper-modal .cropper-line,
    .avatar-cropper-modal .cropper-point {
        display: none !important;
    }
</style>

<div
    id="{{ $modalId }}"
    class="avatar-cropper-modal fixed inset-0 z-[60] hidden items-center justify-center bg-black/80 backdrop-blur-sm px-3 sm:px-4 py-3 sm:py-6"
    style="padding-top: max(env(safe-area-inset-top), 0.75rem); padding-bottom: max(env(safe-area-inset-bottom), 0.75rem);"
>
    <div class="bg-[#0f172a] rounded-2xl w-full max-w-3xl p-4 sm:p-6 shadow-2xl border border-white/10 h-[min(88dvh,720px)] sm:h-[min(92dvh,760px)] overflow-hidden flex flex-col">
        <h2 class="text-white text-xl mb-3 sm:mb-4 shrink-0">Ajusta tu foto</h2>

        <div class="w-full flex-1 min-h-0 h-[min(36dvh,300px)] sm:h-[360px] bg-slate-950/60 rounded-xl overflow-hidden flex items-center justify-center">
            <img id="{{ $cropImageId }}" alt="Editor de avatar" class="max-w-full">
        </div>

        <div class="shrink-0 mt-3 sm:mt-5 bg-[#0f172a] pt-2 pb-[max(env(safe-area-inset-bottom),0px)]">
        <div class="grid grid-cols-3 gap-2.5 sm:flex sm:flex-nowrap sm:justify-end sm:gap-4">
            <button id="{{ $modalId }}_btnReset" type="button" class="min-h-[44px] px-3 sm:px-4 py-2 bg-gray-600 rounded-lg text-white hover:bg-gray-500 transition">
                Reintentar
            </button>

            <button id="{{ $modalId }}_btnCancel" type="button" class="min-h-[44px] px-3 sm:px-4 py-2 bg-red-500 rounded-lg text-white hover:bg-red-400 transition">
                Cancelar
            </button>

            <button id="{{ $modalId }}_btnApply" type="button" class="min-h-[44px] px-3 sm:px-4 py-2 bg-green-500 rounded-lg text-white hover:bg-green-400 transition">
                Aplicar
            </button>
        </div>
        </div>
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
    ];
@endphp

<textarea class="sr-only" data-avatar-cropper-config readonly tabindex="-1" aria-hidden="true">@json($cropperClientConfig)</textarea>
