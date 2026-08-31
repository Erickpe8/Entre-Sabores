import { readdir, stat, writeFile } from 'node:fs/promises';
import path from 'node:path';
import sharp from 'sharp';

const ROOT = path.resolve('public/images');
const TARGET_DIRS = ['illustrations', 'avatars'];

/** Distancia euclídea RGB */
function colorDist(r1, g1, b1, r2, g2, b2) {
    return Math.sqrt((r1 - r2) ** 2 + (g1 - g2) ** 2 + (b1 - b2) ** 2);
}

/** smoothstep 0→1 */
function smoothstep(t) {
    const x = Math.max(0, Math.min(1, t));

    return x * x * (3 - 2 * x);
}

/**
 * Estima color de fondo promediando parches en esquinas y bordes.
 * @param {Buffer} data
 * @param {number} width
 * @param {number} height
 */
function estimateBackgroundColor(data, width, height) {
    const patch = Math.max(3, Math.min(14, Math.floor(Math.min(width, height) * 0.05)));
    const samples = [
        [0, 0],
        [width - patch, 0],
        [0, height - patch],
        [width - patch, height - patch],
        [Math.floor(width / 2) - Math.floor(patch / 2), 0],
        [Math.floor(width / 2) - Math.floor(patch / 2), height - patch],
        [0, Math.floor(height / 2) - Math.floor(patch / 2)],
        [width - patch, Math.floor(height / 2) - Math.floor(patch / 2)],
    ];

    let rSum = 0;
    let gSum = 0;
    let bSum = 0;
    let count = 0;

    for (const [ox, oy] of samples) {
        for (let y = oy; y < oy + patch; y += 1) {
            for (let x = ox; x < ox + patch; x += 1) {
                const idx = (y * width + x) * 4;
                rSum += data[idx];
                gSum += data[idx + 1];
                bSum += data[idx + 2];
                count += 1;
            }
        }
    }

    return {
        r: Math.round(rSum / count),
        g: Math.round(gSum / count),
        b: Math.round(bSum / count),
    };
}

/**
 * Desvanece alpha cerca de los bordes para evitar cortes visibles en difuminados.
 * @param {Buffer} out
 * @param {number} width
 * @param {number} height
 * @param {number} featherPx
 */
function featherEdgeAlpha(out, width, height, featherPx) {
    for (let y = 0; y < height; y += 1) {
        for (let x = 0; x < width; x += 1) {
            const distEdge = Math.min(x, y, width - 1 - x, height - 1 - y);
            if (distEdge >= featherPx) {
                continue;
            }

            const idx = (y * width + x) * 4;
            const factor = smoothstep(distEdge / featherPx);
            out[idx + 3] = Math.round(out[idx + 3] * factor);
        }
    }
}

/**
 * Atenúa halos de estudio (cyan/gris) que quedan pegados al borde del canvas.
 * @param {Buffer} out
 * @param {number} width
 * @param {number} height
 * @param {number} marginPx
 */
function softenEdgeGlow(out, width, height, marginPx) {
    for (let y = 0; y < height; y += 1) {
        for (let x = 0; x < width; x += 1) {
            const distEdge = Math.min(x, y, width - 1 - x, height - 1 - y);
            if (distEdge >= marginPx) {
                continue;
            }

            const idx = (y * width + x) * 4;
            const r = out[idx];
            const g = out[idx + 1];
            const b = out[idx + 2];
            const a = out[idx + 3];

            if (a < 8) {
                continue;
            }

            const isSoftGlow =
                (b > r + 8 && g > r - 5 && r > 160) ||
                (Math.abs(r - g) < 12 && Math.abs(g - b) < 12 && r > 190 && r < 252);

            if (!isSoftGlow) {
                continue;
            }

            const edgeFactor = smoothstep(distEdge / marginPx);
            out[idx + 3] = Math.round(a * edgeFactor * edgeFactor);
        }
    }
}

/**
 * @param {Buffer} input
 * @param {number} padRatio
 */
async function padWithBackground(input, padRatio = 0.2) {
    const { data, info } = await sharp(input).ensureAlpha().raw().toBuffer({ resolveWithObject: true });
    const bg = estimateBackgroundColor(data, info.width, info.height);
    const padX = Math.max(32, Math.round(info.width * padRatio));
    const padY = Math.max(32, Math.round(info.height * padRatio));

    return sharp(input)
        .extend({
            top: padY,
            bottom: padY,
            left: padX,
            right: padX,
            background: { r: bg.r, g: bg.g, b: bg.b, alpha: 255 },
        })
        .png()
        .toBuffer();
}

/**
 * @param {Buffer} input
 * @param {number} padRatio
 */
async function expandTransparentPadding(input, padRatio = 0.14) {
    const meta = await sharp(input).metadata();
    const width = meta.width ?? 0;
    const height = meta.height ?? 0;
    const padX = Math.max(28, Math.round(width * padRatio));
    const padY = Math.max(28, Math.round(height * padRatio));

    return sharp(input)
        .extend({
            top: padY,
            bottom: padY,
            left: padX,
            right: padX,
            background: { r: 0, g: 0, b: 0, alpha: 0 },
        })
        .png()
        .toBuffer();
}

/**
 * @param {Buffer} input
 * @param {{ hardThreshold?: number, softThreshold?: number }} opts
 */
async function removeSolidBackground(input, opts = {}) {
    const hardThreshold = opts.hardThreshold ?? 32;
    const softThreshold = opts.softThreshold ?? 58;

    const { data, info } = await sharp(input).ensureAlpha().raw().toBuffer({ resolveWithObject: true });
    const { width, height } = info;
    const out = Buffer.from(data);
    const bg = estimateBackgroundColor(out, width, height);

    for (let i = 0; i < out.length; i += 4) {
        const r = out[i];
        const g = out[i + 1];
        const b = out[i + 2];
        const dist = colorDist(r, g, b, bg.r, bg.g, bg.b);

        const isNearWhite = r > 235 && g > 235 && b > 235;
        const isFlatGray = Math.abs(r - g) < 10 && Math.abs(g - b) < 10 && r > 190 && r < 252;
        const isSoftStudioGlow = b > r + 10 && g >= r - 8 && r > 150 && dist <= softThreshold + 35;

        if (dist <= hardThreshold || isNearWhite || isSoftStudioGlow) {
            out[i + 3] = 0;
            continue;
        }

        if (dist <= softThreshold || (isFlatGray && dist <= softThreshold + 18)) {
            const t = (dist - hardThreshold) / Math.max(1, softThreshold - hardThreshold);
            out[i + 3] = Math.round(Math.min(out[i + 3], 255 * Math.max(0, Math.min(1, t))));
        }
    }

    const featherPx = Math.max(28, Math.round(Math.min(width, height) * 0.07));
    featherEdgeAlpha(out, width, height, featherPx);
    softenEdgeGlow(out, width, height, Math.round(featherPx * 1.35));

    return sharp(out, { raw: { width, height, channels: 4 } }).png({ compressionLevel: 9 }).toBuffer();
}

/**
 * Post-proceso para PNG ya transparentes: suaviza bordes y añade margen.
 * @param {Buffer} input
 */
async function refineTransparentImage(input) {
    const { data, info } = await sharp(input).ensureAlpha().raw().toBuffer({ resolveWithObject: true });
    const out = Buffer.from(data);
    const featherPx = Math.max(32, Math.round(Math.min(info.width, info.height) * 0.08));

    featherEdgeAlpha(out, info.width, info.height, featherPx);
    softenEdgeGlow(out, info.width, info.height, Math.round(featherPx * 1.4));

    let result = await sharp(out, {
        raw: { width: info.width, height: info.height, channels: 4 },
    })
        .png({ compressionLevel: 9 })
        .toBuffer();

    result = await expandTransparentPadding(result, 0.16);

    return result;
}

/**
 * @param {Buffer} input
 */
async function processIllustrationBuffer(input) {
    const meta = await sharp(input).metadata();
    const hasAlpha = meta.hasAlpha === true && (meta.channels ?? 0) >= 4;

    if (!hasAlpha) {
        const padded = await padWithBackground(input, 0.2);
        let processed = await removeSolidBackground(padded);
        processed = await expandTransparentPadding(processed, 0.12);

        return processed;
    }

    const { data, info } = await sharp(input).ensureAlpha().raw().toBuffer({ resolveWithObject: true });
    let transparent = 0;
    for (let i = 3; i < data.length; i += 4) {
        if (data[i] < 16) {
            transparent += 1;
        }
    }
    const transparentRatio = transparent / (info.width * info.height);

    if (transparentRatio < 0.12) {
        const padded = await padWithBackground(input, 0.2);
        let processed = await removeSolidBackground(padded);
        processed = await expandTransparentPadding(processed, 0.12);

        return processed;
    }

    return refineTransparentImage(input);
}

/**
 * @param {string} dir
 */
async function walkPngFiles(dir) {
    /** @type {string[]} */
    const files = [];

    for (const entry of await readdir(dir, { withFileTypes: true })) {
        const fullPath = path.join(dir, entry.name);
        if (entry.isDirectory()) {
            if (entry.name.startsWith('_')) {
                continue;
            }
            files.push(...(await walkPngFiles(fullPath)));
            continue;
        }

        if (entry.isFile() && entry.name.toLowerCase().endsWith('.png')) {
            files.push(fullPath);
        }
    }

    return files;
}

/**
 * @param {string} pngPath
 */
async function validateAlpha(pngPath) {
    const meta = await sharp(pngPath).metadata();
    if (!meta.hasAlpha) {
        return { ok: false, reason: 'sin alpha' };
    }

    const { data, info } = await sharp(pngPath).ensureAlpha().raw().toBuffer({ resolveWithObject: true });
    const pixels = info.width * info.height;
    let transparent = 0;
    let nearWhiteOpaque = 0;
    let harshEdge = 0;
    const edgeBand = Math.max(8, Math.round(Math.min(info.width, info.height) * 0.04));

    for (let y = 0; y < info.height; y += 1) {
        for (let x = 0; x < info.width; x += 1) {
            const idx = (y * info.width + x) * 4;
            const a = data[idx + 3];

            if (a < 16) {
                transparent += 1;
            }
            if (a > 200 && data[idx] > 240 && data[idx + 1] > 240 && data[idx + 2] > 240) {
                nearWhiteOpaque += 1;
            }

            const distEdge = Math.min(x, y, info.width - 1 - x, info.height - 1 - y);
            if (distEdge < edgeBand && a > 40 && a < 230) {
                harshEdge += 1;
            }
        }
    }

    const transparentRatio = transparent / pixels;
    const whiteRatio = nearWhiteOpaque / pixels;
    const harshEdgeRatio = harshEdge / pixels;

    if (transparentRatio < 0.15) {
        return { ok: false, reason: `poco transparente (${Math.round(transparentRatio * 100)}%)` };
    }
    if (whiteRatio > 0.08) {
        return { ok: false, reason: `fondo blanco residual (${Math.round(whiteRatio * 100)}%)` };
    }
    if (harshEdgeRatio > 0.025) {
        return { ok: false, reason: `difuminado cortado en borde (${Math.round(harshEdgeRatio * 1000) / 10}% px borde)` };
    }

    return { ok: true, transparentRatio };
}

/**
 * @param {string} pngPath
 */
async function processFile(pngPath) {
    const input = await sharp(pngPath).toBuffer();
    const processed = await processIllustrationBuffer(input);
    await writeFile(pngPath, processed);

    const webpPath = pngPath.replace(/\.png$/i, '.webp');
    await sharp(processed).webp({ quality: 85, alphaQuality: 90, effort: 4 }).toFile(webpPath);

    return validateAlpha(pngPath);
}

async function main() {
    const onlyArg = process.argv[2];
    /** @type {string[]} */
    let files = [];

    if (onlyArg) {
        const resolved = path.resolve(onlyArg);
        const st = await stat(resolved);
        if (st.isDirectory()) {
            files = await walkPngFiles(resolved);
        } else {
            files = [resolved];
        }
    } else {
        for (const sub of TARGET_DIRS) {
            const dir = path.join(ROOT, sub);
            try {
                files.push(...(await walkPngFiles(dir)));
            } catch (error) {
                if (/** @type {NodeJS.ErrnoException} */ (error).code !== 'ENOENT') {
                    throw error;
                }
            }
        }
    }

    if (files.length === 0) {
        console.log('No PNG files to process.');
        return;
    }

    let failed = 0;

    for (const file of files.sort()) {
        const rel = path.relative(ROOT, file);
        const before = await stat(file);
        const result = await processFile(file);
        const after = await stat(file);

        if (result.ok) {
            console.log(
                `OK  ${rel} (${Math.round(before.size / 1024)}→${Math.round(after.size / 1024)} KB, ${Math.round((result.transparentRatio ?? 0) * 100)}% transparente)`,
            );
        } else {
            failed += 1;
            console.log(`FAIL ${rel} → ${result.reason}`);
        }
    }

    if (failed > 0) {
        process.exit(1);
    }
}

main().catch((error) => {
    console.error(error);
    process.exit(1);
});
