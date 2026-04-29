import './bootstrap';
import 'flowbite';
import Cropper from 'cropperjs';
import 'cropperjs/dist/cropper.css';

import Alpine from 'alpinejs';

window.Cropper = Cropper;
window.Alpine = Alpine;
Alpine.start();

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
