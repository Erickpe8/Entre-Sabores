import './bootstrap';
import Cropper from 'cropperjs';
import 'cropperjs/dist/cropper.css';

import { initMobileNav } from './ui/mobileNav.js';
import { initDropdowns } from './ui/dropdowns.js';
import { initModals } from './ui/modals.js';
import { initFlashAutoHide } from './ui/flashMessages.js';

window.Cropper = Cropper;

initMobileNav();
initDropdowns();
initModals();
initFlashAutoHide();

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
