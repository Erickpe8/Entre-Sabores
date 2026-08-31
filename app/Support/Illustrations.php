<?php

namespace App\Support;

final class Illustrations
{
    /**
     * @return array{url: string, webp: string|null, png: string, alt: string, width: int|null, height: int|null}|null
     */
    public static function resolve(string $name): ?array
    {
        /** @var array<string, array<string, mixed>>|null $catalog */
        $catalog = config('illustrations.catalog');
        $def = $catalog[$name] ?? null;

        if (! is_array($def)) {
            return null;
        }

        $webpPath = self::firstExistingPath($def, ['webp']);
        $pngPath = self::firstExistingPath($def, ['png', 'legacy']);
        // PNG con alpha es la fuente de verdad; WebP solo si existe derivado validado.
        $preferPng = ($def['prefer_png'] ?? true) === true;
        $primaryPath = $preferPng ? ($pngPath ?? $webpPath) : ($webpPath ?? $pngPath);

        if ($primaryPath === null) {
            return null;
        }

        return [
            'url' => asset($primaryPath),
            'webp' => $webpPath !== null ? asset($webpPath) : null,
            'png' => $pngPath !== null ? asset($pngPath) : asset($primaryPath),
            'alt' => (string) ($def['alt'] ?? ''),
            'width' => isset($def['width']) ? (int) $def['width'] : null,
            'height' => isset($def['height']) ? (int) $def['height'] : null,
        ];
    }

    /**
     * @param  list<string>  $names
     * @return array<string, array{url: string, webp: string|null, png: string, alt: string}>
     */
    public static function bundleForJs(array $names): array
    {
        $bundle = [];

        foreach ($names as $name) {
            $resolved = self::resolve($name);
            if ($resolved === null) {
                continue;
            }

            $bundle[$name] = [
                'url' => $resolved['url'],
                'webp' => $resolved['webp'],
                'png' => $resolved['png'],
                'alt' => $resolved['alt'],
            ];
        }

        return $bundle;
    }

    /** Ruta pública relativa del avatar predeterminado (PNG con alpha). */
    public static function defaultAvatarPath(): string
    {
        /** @var array<string, mixed>|null $def */
        $def = config('illustrations.catalog.avatar-default-foodie');

        if (! is_array($def)) {
            return '/images/default.png';
        }

        foreach (['png', 'webp', 'legacy'] as $key) {
            $path = $def[$key] ?? null;
            if (is_string($path) && $path !== '' && is_file(public_path($path))) {
                return '/'.ltrim(str_replace('\\', '/', $path), '/');
            }
        }

        return '/images/default.png';
    }

    /**
     * @param  array<string, mixed>  $def
     * @param  list<string>  $keys
     */
    private static function firstExistingPath(array $def, array $keys): ?string
    {
        foreach ($keys as $key) {
            $path = $def[$key] ?? null;
            if (! is_string($path) || $path === '') {
                continue;
            }

            if (is_file(public_path($path))) {
                return $path;
            }
        }

        return null;
    }
}
