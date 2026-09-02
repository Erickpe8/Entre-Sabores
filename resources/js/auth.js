import './bootstrap-core.js';
import '@fontsource/plus-jakarta-sans/400.css';
import '@fontsource/plus-jakarta-sans/500.css';
import '@fontsource/plus-jakarta-sans/600.css';
import '@fontsource/plus-jakarta-sans/700.css';
import Cropper from 'cropperjs';
import 'cropperjs/dist/cropper.css';

import { initAlerts } from './ui/alerts.js';
import { initFlashAutoHide } from './ui/flashMessages.js';
import { initAuthForms } from './ui/authForms.js';
import { initRegisterWizard } from './ui/registerWizard.js';
import { initRegisterCountryLocation } from './ui/registerCountryLocation.js';
import { initUsernameAvailability } from './ui/usernameAvailability.js';
import { initAvatarCroppers } from './ui/avatarCropper.js';

window.Cropper = Cropper;

initAlerts();
initFlashAutoHide();
initAuthForms();
initRegisterCountryLocation();
initRegisterWizard();
initUsernameAvailability();
initAvatarCroppers();
