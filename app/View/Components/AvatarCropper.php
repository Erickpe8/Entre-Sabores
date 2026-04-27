<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

class AvatarCropper extends Component
{
    /**
     * @param  'register'|'profile'  $mode
     * @param  'dynamic'|'persistent'  $cropSource
     */
    public function __construct(
        public string $mode = 'profile',
        public string $previewId = 'avatarPreview',
        public string $base64InputId = 'profile_photo_base64',
        public string $openButtonId = 'openAvatarModal',
        public string $cropImageId = 'cropperImage',
        public string $modalId = 'avatarCropModal',
        public string $cropSource = 'persistent',
        public ?string $cropSourceInputId = null,
        public ?string $dataTransferInputId = null,
        public ?string $formId = null,
        public ?string $stepOneId = null,
        public ?string $stepTwoId = null,
    ) {}

    public function render(): View
    {
        return view('components.avatar-cropper');
    }
}
