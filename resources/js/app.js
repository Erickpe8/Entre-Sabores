import './bootstrap';
import Cropper from 'cropperjs';
import 'cropperjs/dist/cropper.css';

import { initAppChromePageshow, resetAppChromeState } from './ui/appChromeReset.js';
import { initMobileNav } from './ui/mobileNav.js';
import { initDropdowns } from './ui/dropdowns.js';
import { initModals } from './ui/modals.js';
import { initFlashAutoHide } from './ui/flashMessages.js';
import { initProfileShareModal } from './ui/profileShareModal.js';
import { initProfilePage } from './ui/profilePage.js';
import { initUsernameAvailability } from './ui/usernameAvailability.js';
import { initAvatarCroppers } from './ui/avatarCropper.js';

window.Cropper = Cropper;

resetAppChromeState();
initAppChromePageshow();

initMobileNav();
initDropdowns();
initModals();
initFlashAutoHide();
initProfileShareModal();
initProfilePage();
initUsernameAvailability();
initAvatarCroppers();

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
