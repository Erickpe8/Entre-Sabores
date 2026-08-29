/** @type {HTMLElement | null} */
let lightboxRoot = null;

function ensureLightbox() {
    if (lightboxRoot) {
        return lightboxRoot;
    }

    lightboxRoot = document.createElement('div');
    lightboxRoot.id = 'post-image-lightbox';
    lightboxRoot.className =
        'fixed inset-0 z-[70] hidden items-center justify-center bg-black/85 p-4 backdrop-blur-sm';
    lightboxRoot.setAttribute('role', 'dialog');
    lightboxRoot.setAttribute('aria-modal', 'true');
    lightboxRoot.setAttribute('aria-label', 'Imagen ampliada');
    lightboxRoot.innerHTML = `
        <button type="button" class="post-image-lightbox-close absolute right-4 top-4 inline-flex min-h-10 min-w-10 items-center justify-center rounded-base text-heading hover:text-accent-warm focus:outline-none focus:ring-4 focus:ring-neutral-tertiary" aria-label="Cerrar">
            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
        <img class="post-image-lightbox-img max-h-[90vh] max-w-full rounded-base object-contain shadow-lg" alt="" />
    `;

    lightboxRoot.addEventListener('click', (e) => {
        if (e.target === lightboxRoot || e.target.closest('.post-image-lightbox-close')) {
            closePostImageLightbox();
        }
    });

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && lightboxRoot && !lightboxRoot.classList.contains('hidden')) {
            closePostImageLightbox();
        }
    });

    document.body.appendChild(lightboxRoot);

    return lightboxRoot;
}

export function openPostImageLightbox(url) {
    if (!url) {
        return;
    }

    const root = ensureLightbox();
    const img = root.querySelector('.post-image-lightbox-img');
    if (img instanceof HTMLImageElement) {
        img.src = url;
        img.alt = '';
    }
    root.classList.remove('hidden');
    root.classList.add('flex');
    document.body.classList.add('overflow-hidden');
}

export function closePostImageLightbox() {
    if (!lightboxRoot) {
        return;
    }

    lightboxRoot.classList.add('hidden');
    lightboxRoot.classList.remove('flex');
    const img = lightboxRoot.querySelector('.post-image-lightbox-img');
    if (img instanceof HTMLImageElement) {
        img.removeAttribute('src');
    }
    document.body.classList.remove('overflow-hidden');
}

/**
 * @param {HTMLImageElement} img
 */
function detectLightImage(img) {
    if (img.dataset.lightContent === '1') {
        return true;
    }

    try {
        const canvas = document.createElement('canvas');
        const size = 24;
        canvas.width = size;
        canvas.height = size;
        const ctx = canvas.getContext('2d');
        if (!ctx) {
            return false;
        }
        ctx.drawImage(img, 0, 0, size, size);
        const { data } = ctx.getImageData(0, 0, size, size);
        let lightPixels = 0;
        const total = data.length / 4;
        for (let i = 0; i < data.length; i += 4) {
            const luminance = 0.299 * data[i] + 0.587 * data[i + 1] + 0.114 * data[i + 2];
            if (luminance > 210) {
                lightPixels += 1;
            }
        }

        return lightPixels / total > 0.52;
    } catch {
        return false;
    }
}

/**
 * @param {HTMLImageElement} img
 */
function finalizePostCardImage(img) {
    const media = img.closest('.post-card-media');
    if (!media) {
        return;
    }

    media.classList.remove('post-card-media--loading');

    if (detectLightImage(img)) {
        media.classList.add('post-card-media--light');
    }
}

/**
 * @param {HTMLElement} cardEl
 */
export function enhancePostCardMedia(cardEl) {
    cardEl.querySelectorAll('.post-card-media__img').forEach((node) => {
        if (!(node instanceof HTMLImageElement)) {
            return;
        }

        const finish = () => finalizePostCardImage(node);
        if (node.complete && node.naturalWidth > 0) {
            finish();
        } else {
            node.addEventListener('load', finish, { once: true });
            node.addEventListener('error', () => {
                node.closest('.post-card-media')?.classList.remove('post-card-media--loading');
            }, { once: true });
        }
    });
}

/**
 * @param {{ image_url?: string | null, image_light_content?: boolean }} post
 * @param {string} esc — escape HTML helper
 */
export function buildPostImageHtml(post, esc) {
    if (!post.image_url) {
        return '';
    }

    const lightFlag = post.image_light_content === true ? ' data-light-content="1"' : '';

    return `
        <div class="post-card-media post-card-media--loading my-3 overflow-hidden rounded-base border border-default">
            <div class="post-card-media__shimmer" aria-hidden="true"></div>
            <button type="button" class="post-card-media-open relative block w-full cursor-zoom-in focus:outline-none focus:ring-4 focus:ring-neutral-tertiary focus:ring-inset" data-image-url="${esc(post.image_url)}" aria-label="Ver imagen ampliada">
                <span class="post-card-media__inner block">
                    <img src="${esc(post.image_url)}" alt="" class="post-card-media__img max-h-80 w-full object-cover"${lightFlag} loading="lazy" decoding="async" />
                </span>
            </button>
        </div>`;
}

/** @param {HTMLElement | Document} root */
export function initPostCardMedia(root = document) {
    const host = root instanceof Document ? root.documentElement : root;
    if (!(host instanceof HTMLElement) || host.dataset.postCardMediaBound === '1') {
        return;
    }

    host.dataset.postCardMediaBound = '1';
    ensureLightbox();

    host.addEventListener('click', (e) => {
        const trigger = e.target.closest('.post-card-media-open');
        if (!trigger?.dataset.imageUrl) {
            return;
        }
        e.preventDefault();
        e.stopPropagation();
        openPostImageLightbox(trigger.dataset.imageUrl);
    });
}
