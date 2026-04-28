import './bootstrap';
import Cropper from 'cropperjs';
import 'cropperjs/dist/cropper.css';

import Alpine from 'alpinejs';

window.Cropper = Cropper;
window.Alpine = Alpine;

Alpine.start();

if (document.getElementById('posts-container')) {
    import('./wall.js').then((m) => m.initWall());
}
