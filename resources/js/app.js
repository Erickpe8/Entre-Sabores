import './bootstrap';
import '@fontsource/plus-jakarta-sans/400.css';
import '@fontsource/plus-jakarta-sans/500.css';
import '@fontsource/plus-jakarta-sans/600.css';
import '@fontsource/plus-jakarta-sans/700.css';
import Cropper from 'cropperjs';
import 'cropperjs/dist/cropper.css';

import { initFlowbite } from 'flowbite';

import { initAppChromePageshow, resetAppChromeState } from './ui/appChromeReset.js';
import { initDropdowns } from './ui/dropdowns.js';
import { initModals } from './ui/modals.js';
import { initFlashAutoHide } from './ui/flashMessages.js';
import { initAlerts } from './ui/alerts.js';
import { initProfileShareModal } from './ui/profileShareModal.js';
import { initProfilePage } from './ui/profilePage.js';
import { initUsernameAvailability } from './ui/usernameAvailability.js';
import { initNavbarChrome } from './ui/navbarChrome.js';
import { initAvatarCroppers } from './ui/avatarCropper.js';
import { initAuthForms } from './ui/authForms.js';
import { initRegisterWizard } from './ui/registerWizard.js';
import { initRegisterCountryLocation } from './ui/registerCountryLocation.js';

window.Cropper = Cropper;

resetAppChromeState();
initAppChromePageshow();
initFlowbite();

document.addEventListener('DOMContentLoaded', () => initFlowbite());
window.addEventListener('pageshow', () => initFlowbite());
initDropdowns();
initModals();
initFlashAutoHide();
initAlerts();
initProfileShareModal();
initProfilePage();
initUsernameAvailability();
initAuthForms();
initRegisterCountryLocation();
initRegisterWizard();
initAvatarCroppers();
initNavbarChrome();

if (document.getElementById('posts-container')) {
    import('./wall.js').then((m) => m.initWall());
}

if (document.getElementById('post-show-page')) {
    import('./post-show.js').then((m) => m.initPostShow());
}

if (document.getElementById('profile-posts-grid')) {
    import('./profilePosts.js').then((m) => m.initProfilePosts());
}

if (document.getElementById('nav-notifications-root')) {
    import('./notificationsNav.js').then((m) => m.initNotificationsNav());
}
