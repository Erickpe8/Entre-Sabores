/** @param {number} postId @param {(message: string, variant?: string) => void} [onNotify] */
export async function sharePost(postId, onNotify) {
    const url = `${window.location.origin}/posts/${postId}`;

    try {
        if (navigator.share) {
            await navigator.share({ url, title: 'Entre Sabores' });

            return;
        }

        await navigator.clipboard.writeText(url);
        onNotify?.('Enlace copiado al portapapeles', 'success');
    } catch (error) {
        if (error?.name === 'AbortError') {
            return;
        }
        onNotify?.('No pudimos copiar el enlace.', 'error');
    }
}
