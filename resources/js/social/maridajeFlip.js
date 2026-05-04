import { ensureEcho } from '../echo.js';
import { esc } from './postCard.js';

/**
 * @typedef {{ historia?: string, afinidad?: string, equilibrio?: string, recomendacion?: string, score?: number }} AiAnalysis
 */

function buildFrontToolbarHtml() {
    return `
        <div class="maridaje-front-toolbar flex justify-end border-t border-slate-700/60 pt-3 mt-1">
            <button type="button" class="maridaje-btn-show-analysis inline-flex items-center gap-2 rounded-full bg-emerald-600/90 px-4 py-2 text-xs font-semibold text-white shadow-sm shadow-emerald-900/30 transition hover:bg-emerald-500 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-400/70">
                Ver análisis
            </button>
        </div>`;
}

/**
 * @param {boolean} canReanalyze
 */
function buildBackToolbarHtml(canReanalyze) {
    return `
        <div class="maridaje-analysis-toolbar flex shrink-0 flex-wrap items-center justify-between gap-2 border-b border-slate-700/70 px-5 pb-3 pt-4">
            <button type="button" class="maridaje-btn-back-analysis inline-flex items-center gap-1 rounded-full border border-slate-600/80 bg-slate-800/80 px-3 py-1.5 text-xs font-medium text-slate-200 transition hover:bg-slate-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-400/60">
                ← Volver
            </button>
            ${
                canReanalyze
                    ? `<button type="button" class="maridaje-btn-reanalyze-analysis inline-flex items-center gap-2 rounded-full bg-violet-600/90 px-3 py-1.5 text-xs font-semibold text-white shadow-sm transition hover:bg-violet-500 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-violet-400/70">
                Reanalizar maridaje
            </button>`
                    : ''
            }
        </div>`;
}

/**
 * @param {AiAnalysis|null|undefined} analysis
 */
export function renderAiAnalysisSectionsHtml(analysis) {
    if (!analysis || typeof analysis !== 'object') {
        return `
            <div class="flex flex-col items-center justify-center gap-4 py-10 px-2">
                <div class="h-11 w-11 rounded-full border-2 border-emerald-500/25 border-t-emerald-400 animate-spin" aria-hidden="true"></div>
                <p class="text-center text-sm font-medium text-slate-300 animate-pulse">Analizando maridaje…</p>
                <p class="text-center text-xs text-slate-500 max-w-xs">Te avisaremos en esta vista cuando el análisis esté listo (tiempo real).</p>
            </div>`;
    }

    const historia = esc(String(analysis.historia ?? ''));
    const afinidad = esc(String(analysis.afinidad ?? ''));
    const equilibrio = esc(String(analysis.equilibrio ?? ''));
    const recomendacion = esc(String(analysis.recomendacion ?? ''));
    const scoreNum = Math.min(10, Math.max(1, Number(analysis.score) || 1));

    return `
        <div class="post-maridaje-ai-body space-y-4 text-sm leading-relaxed text-slate-200">
            <h4 class="flex items-center gap-2 text-base font-semibold text-emerald-300">
                <span aria-hidden="true">🧠</span> Análisis del maridaje
            </h4>
            <div class="space-y-1">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Historia</p>
                <p class="text-slate-300">${historia}</p>
            </div>
            <div class="space-y-1">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Afinidad</p>
                <p class="text-slate-300">${afinidad}</p>
            </div>
            <div class="space-y-1">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Equilibrio</p>
                <p class="text-slate-300">${equilibrio}</p>
            </div>
            <div class="space-y-1">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Recomendación</p>
                <p class="text-slate-300">${recomendacion}</p>
            </div>
            <p class="pt-2 text-base font-semibold text-amber-300 border-t border-slate-700/80">
                <span aria-hidden="true">⭐</span> Score: ${scoreNum} / 10
            </p>
        </div>`;
}

/**
 * @param {{
 *   postId: number,
 *   heroImg: string,
 *   userHeaderModal: string,
 *   tagsLine: string,
 *   titleHtml: string,
 *   descriptionStoryHtml: string,
 *   likeToolbar: string,
 *   aiAnalysis: AiAnalysis|null,
 *   canReanalyze: boolean
 * }} p
 */
export function buildWallModalFlipHtml(p) {
    const slotInner = renderAiAnalysisSectionsHtml(p.aiAnalysis);

    return `
        <div data-maridaje-flip-root data-maridaje-post-id="${p.postId}" class="mb-1">
            <p class="mb-3 text-center text-[11px] text-slate-500">
                Usa <span class="font-medium text-slate-400">Ver análisis</span> y <span class="font-medium text-slate-400">Volver</span> para moverte entre la publicación y la IA.
            </p>
            <div class="post-maridaje-flip-scene rounded-xl ring-1 ring-slate-700/45">
                <div class="post-maridaje-flip-inner rounded-xl">
                    <div class="post-maridaje-front rounded-xl border border-slate-700/80 bg-slate-900/60 shadow-inner shadow-black/30 overflow-hidden">
                        ${p.heroImg}
                        <div class="space-y-4 p-4">
                            ${p.userHeaderModal}
                            <div>
                                <h2 class="text-xl font-bold text-slate-50">${p.titleHtml}</h2>
                                <div class="mt-2 flex flex-wrap gap-2">${p.tagsLine}</div>
                            </div>
                            <div class="text-sm leading-relaxed text-slate-300 whitespace-pre-wrap">${p.descriptionStoryHtml}</div>
                            ${buildFrontToolbarHtml()}
                            ${p.likeToolbar}
                        </div>
                    </div>
                    <div class="post-maridaje-back flex max-h-[min(480px,65vh)] flex-col rounded-xl border border-emerald-900/40 bg-slate-950/95 shadow-inner shadow-black/40">
                        ${buildBackToolbarHtml(p.canReanalyze)}
                        <div class="min-h-0 flex-1 overflow-y-auto overscroll-contain px-5 pb-5 touch-pan-y" data-maridaje-ai-slot>
                            ${slotInner}
                        </div>
                    </div>
                </div>
            </div>
        </div>`;
}

/**
 * @param {HTMLElement} mountEl
 * @param {object} post
 * @param {HTMLElement} articleEl
 * @param {{
 *   axios: import('axios').AxiosStatic,
 *   postBaseUrl: string,
 *   authUserId?: number|null,
 *   onNotify?: (message: string, variant?: string) => void
 * }} io
 */
export function mountPostShowMaridajeFlip(mountEl, post, articleEl, io) {
    mountEl.innerHTML = '';

    const canReanalyze =
        io.authUserId != null && Number(io.authUserId) === Number(post.user?.id ?? NaN);

    const root = document.createElement('div');
    root.dataset.maridajeFlipRoot = '';
    root.dataset.maridajePostId = String(post.id);
    root.className = 'mb-6';

    const hint = document.createElement('p');
    hint.className = 'mb-3 text-center text-[11px] text-slate-500';
    hint.textContent =
        'Usa Ver análisis y Volver para moverte entre la publicación y la IA.';

    const scene = document.createElement('div');
    scene.className = 'post-maridaje-flip-scene rounded-xl ring-1 ring-slate-700/45';

    const inner = document.createElement('div');
    inner.className = 'post-maridaje-flip-inner rounded-xl';

    const front = document.createElement('div');
    front.className =
        'post-maridaje-front rounded-xl border border-slate-700/80 bg-slate-900/60 shadow-inner shadow-black/30 overflow-hidden';

    articleEl.classList.add('w-full');
    front.appendChild(articleEl);

    const toolbarHost = document.createElement('div');
    toolbarHost.className = 'border-t border-slate-700/60 bg-slate-900/55 px-4 pb-4 pt-2';
    toolbarHost.innerHTML = buildFrontToolbarHtml();
    front.appendChild(toolbarHost);

    const back = document.createElement('div');
    back.className =
        'post-maridaje-back flex max-h-[min(520px,70vh)] flex-col rounded-xl border border-emerald-900/40 bg-slate-950/95 shadow-inner shadow-black/40';
    back.innerHTML =
        buildBackToolbarHtml(canReanalyze) +
        `<div class="min-h-0 flex-1 overflow-y-auto overscroll-contain px-5 pb-5 touch-pan-y" data-maridaje-ai-slot>${renderAiAnalysisSectionsHtml(post.ai_analysis ?? null)}</div>`;

    inner.appendChild(front);
    inner.appendChild(back);
    scene.appendChild(inner);
    root.appendChild(hint);
    root.appendChild(scene);
    mountEl.appendChild(root);

    const reanalyzeUrl = `${io.postBaseUrl.replace(/\/$/, '')}/${post.id}/reanalyze`;

    return bindMaridajeFlip(root, {
        postId: Number(post.id),
        axios: io.axios,
        reanalyzeUrl,
        canReanalyze,
        initialAnalysis: post.ai_analysis ?? null,
        onNotify: io.onNotify,
    });
}

/**
 * @typedef {{
 *   postId: number,
 *   axios?: import('axios').AxiosStatic,
 *   reanalyzeUrl?: string,
 *   canReanalyze?: boolean,
 *   initialAnalysis?: AiAnalysis|null,
 *   onNotify?: (message: string, variant?: string) => void
 * }} MaridajeFlipOpts
 */

/**
 * @param {HTMLElement} sceneRoot — elemento con [data-maridaje-flip-root]
 * @param {MaridajeFlipOpts} opts
 * @returns {() => void}
 */
export function bindMaridajeFlip(sceneRoot, opts) {
    const inner = sceneRoot.querySelector('.post-maridaje-flip-inner');
    const slot = sceneRoot.querySelector('[data-maridaje-ai-slot]');

    if (!inner || !slot) {
        return () => {};
    }

    let latestAnalysis = opts.initialAnalysis ?? null;

    function showFront() {
        inner.classList.remove('is-maridaje-flipped');
    }

    function showBack() {
        inner.classList.add('is-maridaje-flipped');
    }

    function onWsAnalysis(payload) {
        if (Number(payload?.post_id) !== Number(opts.postId)) {
            return;
        }
        const a = payload?.ai_analysis;
        if (!a || typeof a !== 'object') {
            return;
        }
        latestAnalysis = a;
        slot.innerHTML = renderAiAnalysisSectionsHtml(a);
    }

    /** @param {MouseEvent} e */
    function onRootClick(e) {
        const showBtn = e.target.closest('.maridaje-btn-show-analysis');
        if (showBtn) {
            e.preventDefault();
            e.stopPropagation();
            showBack();

            return;
        }

        const backBtn = e.target.closest('.maridaje-btn-back-analysis');
        if (backBtn) {
            e.preventDefault();
            e.stopPropagation();
            showFront();

            return;
        }

        const reBtn = e.target.closest('.maridaje-btn-reanalyze-analysis');
        if (reBtn && opts.axios && opts.reanalyzeUrl) {
            e.preventDefault();
            e.stopPropagation();
            showBack();
            slot.innerHTML = renderAiAnalysisSectionsHtml(null);
            void (async () => {
                try {
                    await opts.axios.post(opts.reanalyzeUrl);
                    opts.onNotify?.('Reanálisis en curso…', 'info');
                } catch {
                    opts.onNotify?.('No se pudo solicitar el reanálisis.', 'error');
                    slot.innerHTML = renderAiAnalysisSectionsHtml(latestAnalysis);
                }
            })();
        }
    }

    sceneRoot.addEventListener('click', onRootClick);

    let echoChannel = null;
    const wsHandler = (payload) => {
        onWsAnalysis(payload);
    };
    const Echo = ensureEcho();
    if (Echo != null && opts.postId != null) {
        echoChannel = Echo.channel(`post.${opts.postId}`);
        echoChannel.listen('.post.analysis.generated', wsHandler);
    }

    return () => {
        sceneRoot.removeEventListener('click', onRootClick);
        inner.classList.remove('is-maridaje-flipped');
        if (echoChannel != null) {
            echoChannel.stopListening('.post.analysis.generated', wsHandler);
        }
    };
}
