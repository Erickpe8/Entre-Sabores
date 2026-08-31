import { readdir, stat } from 'node:fs/promises';
import path from 'node:path';
import sharp from 'sharp';

const ROOT = path.resolve('public/images');
const TARGET_DIRS = ['heroes', 'illustrations'];

/**
 * @param {string} dir
 */
async function walkPngFiles(dir) {
    /** @type {string[]} */
    const files = [];

    for (const entry of await readdir(dir, { withFileTypes: true })) {
        const fullPath = path.join(dir, entry.name);
        if (entry.isDirectory()) {
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
async function convertOne(pngPath) {
    const webpPath = pngPath.replace(/\.png$/i, '.webp');
    const pngStat = await stat(pngPath);

    try {
        const webpStat = await stat(webpPath);
        if (webpStat.mtimeMs >= pngStat.mtimeMs) {
            console.log(`skip ${path.relative(ROOT, webpPath)} (up to date)`);

            return;
        }
    } catch {
        // webp missing — convert
    }

    await sharp(pngPath)
        .webp({ quality: 85, alphaQuality: 80, effort: 4 })
        .toFile(webpPath);

    const outStat = await stat(webpPath);
    const saved = pngStat.size - outStat.size;
    const pct = pngStat.size > 0 ? Math.round((saved / pngStat.size) * 100) : 0;

    console.log(
        `ok   ${path.relative(ROOT, pngPath)} → ${path.relative(ROOT, webpPath)} (${Math.round(outStat.size / 1024)} KB, −${pct}%)`,
    );
}

async function main() {
    /** @type {string[]} */
    const pngFiles = [];

    for (const sub of TARGET_DIRS) {
        const dir = path.join(ROOT, sub);
        try {
            pngFiles.push(...(await walkPngFiles(dir)));
        } catch (error) {
            if (/** @type {NodeJS.ErrnoException} */ (error).code !== 'ENOENT') {
                throw error;
            }
        }
    }

    if (pngFiles.length === 0) {
        console.log('No PNG files found under heroes/ or illustrations/.');

        return;
    }

    for (const pngPath of pngFiles.sort()) {
        await convertOne(pngPath);
    }
}

main().catch((error) => {
    console.error(error);
    process.exit(1);
});
