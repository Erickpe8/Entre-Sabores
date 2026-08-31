import { readdir, stat } from 'node:fs/promises';
import path from 'node:path';
import sharp from 'sharp';

const ROOT = path.resolve('public/images');
const TARGET_DIRS = ['heroes', 'illustrations', 'avatars'];

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
async function validateOne(pngPath) {
    const meta = await sharp(pngPath).metadata();
    const { width = 0, height = 0, channels = 0, hasAlpha = false } = meta;

    if (!hasAlpha || channels < 4) {
        return {
            ok: false,
            reason: 'sin canal alpha (RGB, no RGBA)',
            opaqueRatio: 1,
        };
    }

    const { data, info } = await sharp(pngPath).ensureAlpha().raw().toBuffer({ resolveWithObject: true });
    const pixels = info.width * info.height;
    let transparent = 0;
    let nearWhiteOpaque = 0;
    let nearGrayOpaque = 0;

    for (let i = 0; i < data.length; i += 4) {
        const r = data[i];
        const g = data[i + 1];
        const b = data[i + 2];
        const a = data[i + 3];

        if (a < 16) {
            transparent += 1;
            continue;
        }

        const isNearWhite = r > 240 && g > 240 && b > 240;
        const isNearGray = Math.abs(r - g) < 12 && Math.abs(g - b) < 12 && r > 200 && r < 250;

        if (isNearWhite) {
            nearWhiteOpaque += 1;
        }
        if (isNearGray) {
            nearGrayOpaque += 1;
        }
    }

    const transparentRatio = transparent / pixels;
    const whiteRatio = nearWhiteOpaque / pixels;
    const grayRatio = nearGrayOpaque / pixels;
    const opaqueRatio = 1 - transparentRatio;

    /** Esquinas: detectar “lienzo” rectangular */
    const corners = [
        [0, 0],
        [width - 1, 0],
        [0, height - 1],
        [width - 1, height - 1],
    ];
    let cornerOpaque = 0;
    for (const [x, y] of corners) {
        const idx = (y * width + x) * 4;
        if (data[idx + 3] > 200) {
            cornerOpaque += 1;
        }
    }

    const issues = [];

    if (transparentRatio < 0.08) {
        issues.push(`poco transparente (${Math.round(transparentRatio * 100)}% px transparentes)`);
    }
    if (whiteRatio > 0.12) {
        issues.push(`fondo blanco probable (${Math.round(whiteRatio * 100)}% px casi blancos opacos)`);
    }
    if (grayRatio > 0.15) {
        issues.push(`fondo gris probable (${Math.round(grayRatio * 100)}% px grises opacos)`);
    }
    if (cornerOpaque >= 3 && transparentRatio < 0.2) {
        issues.push('esquinas opacas (posible lienzo rectangular)');
    }

    return {
        ok: issues.length === 0,
        reason: issues.join('; ') || 'ok',
        transparentRatio,
        whiteRatio,
        grayRatio,
        opaqueRatio,
        cornerOpaque,
    };
}

async function main() {
    /** @type {string[]} */
    const files = [];

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

    if (files.length === 0) {
        console.log('No PNG files found.');
        return;
    }

    let failed = 0;

    for (const file of files.sort()) {
        const rel = path.relative(ROOT, file);
        const result = await validateOne(file);
        const sizeKb = Math.round((await stat(file)).size / 1024);

        if (result.ok) {
            console.log(`PASS  ${rel} (${sizeKb} KB, ${Math.round(result.transparentRatio * 100)}% transparente)`);
        } else {
            failed += 1;
            console.log(`FAIL  ${rel} (${sizeKb} KB) → ${result.reason}`);
        }
    }

    if (failed > 0) {
        console.log(`\n${failed} archivo(s) no cumplen el requisito de transparencia.`);
        process.exit(1);
    }

    console.log(`\nTodos los PNG validados (${files.length}).`);
}

main().catch((error) => {
    console.error(error);
    process.exit(1);
});
