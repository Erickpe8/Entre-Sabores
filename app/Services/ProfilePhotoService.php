<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Encoders\JpegEncoder;
use Intervention\Image\Encoders\WebpEncoder;
use Intervention\Image\ImageManager;
use RuntimeException;
use Throwable;

/**
 * Encoda avatares a WebP en servidor (sin procesamiento en el navegador).
 */
class ProfilePhotoService
{
    public function __construct(
        private readonly ImageManager $images,
    ) {}

    /**
     * Guarda variantes WebP bajo $directory (relativo al disco public).
     * Devuelve la ruta canónica guardada en BD: …/avatar.webp (full 300×300).
     */
    public function storeFromUploadedFile(UploadedFile $file, string $directory): string
    {
        $realPath = $file->getRealPath();
        if ($realPath === false) {
            throw new RuntimeException('No se pudo leer el archivo subido.');
        }

        $binary = file_get_contents($realPath);
        if ($binary === false) {
            throw new RuntimeException('No se pudo leer el archivo subido.');
        }

        if (! $this->canProcessProfileVariants()) {
            return $this->storeOriginalUploadedFile($file, $binary, $directory);
        }

        return $this->storeFromBinary($binary, $directory);
    }

    /**
     * @param  string  $directory  ej. profiles/usuario123 (sin barra final)
     */
    public function storeFromBinary(string $binary, string $directory): string
    {
        $directory = trim($directory, '/');
        $disk = Storage::disk('public');

        $quality = (int) config('profile_photo.quality', 78);
        $quality = max(1, min(100, $quality));

        $sizes = config('profile_photo.sizes', []);
        $filenames = $this->resolveTargetFilenames((array) config('profile_photo.filenames', []));
        $encoder = $this->resolveEncoder($quality);

        if ($encoder === null) {
            throw new RuntimeException('No hay encoder disponible para procesar imágenes de perfil.');
        }

        $disk->makeDirectory($directory);

        foreach (['thumb', 'medium', 'full'] as $key) {
            $dim = (int) ($sizes[$key] ?? 0);
            $name = (string) ($filenames[$key] ?? '');
            if ($dim < 1 || $name === '') {
                continue;
            }

            try {
                $encoded = $this->images
                    ->decodeBinary($binary)
                    ->cover($dim, $dim)
                    ->encode($encoder);
            } catch (Throwable $e) {
                throw new RuntimeException('No se pudo procesar la imagen del perfil.', 0, $e);
            }

            $disk->put($directory.'/'.$name, (string) $encoded);
        }

        $fullName = (string) ($filenames['full'] ?? 'avatar.webp');

        return $directory.'/'.$fullName;
    }

    /**
     * Elimina la foto principal y las variantes conocidas; también intenta borrar legados (jpg/png).
     */
    public function deleteStoredPhoto(?string $storedPath): void
    {
        if ($storedPath === null || $storedPath === '') {
            return;
        }

        $disk = Storage::disk('public');
        $normalized = User::normalizePublicDiskPath($storedPath) ?? $storedPath;
        $normalized = trim(str_replace('\\', '/', $normalized), '/');

        if ($normalized === '') {
            return;
        }

        $dir = dirname($normalized);
        $base = pathinfo($normalized, PATHINFO_FILENAME);
        $ext = strtolower((string) pathinfo($normalized, PATHINFO_EXTENSION));

        $disk->delete($normalized);

        foreach ([
            config('profile_photo.filenames.medium', 'avatar_medium.webp'),
            config('profile_photo.filenames.thumb', 'avatar_thumb.webp'),
        ] as $variant) {
            $disk->delete($dir.'/'.ltrim((string) $variant, '/'));
            $disk->delete($dir.'/'.ltrim((string) $this->replaceExtension((string) $variant, 'jpg'), '/'));
        }

        if ($base === 'avatar' && $ext === 'webp') {
            return;
        }

        // Legado: otros nombres en la misma carpeta del usuario (avatar_timestamp.jpg, etc.)
        if ($dir !== '.' && $disk->exists($dir)) {
            foreach ($disk->files($dir) as $pathInDir) {
                $leaf = basename((string) $pathInDir);
                if (preg_match('/^avatar_.+\.(jpe?g|png)$/i', $leaf)) {
                    $disk->delete($pathInDir);
                }
            }
        }
    }

    /**
     * Convierte una imagen ya existente en disco public (ruta relativa) a las variantes WebP y devuelve la nueva ruta principal.
     */
    public function convertExistingFileToWebpVariants(string $relativePath): string
    {
        $disk = Storage::disk('public');
        $relativePath = trim(str_replace('\\', '/', $relativePath), '/');

        if (! $disk->exists($relativePath)) {
            throw new RuntimeException("No existe el archivo: {$relativePath}");
        }

        $binary = $disk->get($relativePath);
        $directory = dirname($relativePath);

        $newPath = $this->storeFromBinary($binary, $directory === '.' ? '' : $directory);

        if ($newPath !== $relativePath && $disk->exists($relativePath)) {
            $disk->delete($relativePath);
        }

        return $newPath;
    }

    private function resolveEncoder(int $quality): WebpEncoder|JpegEncoder|null
    {
        if ($this->canEncodeWebp()) {
            return new WebpEncoder(quality: $quality);
        }

        if ($this->canEncodeJpeg()) {
            return new JpegEncoder(quality: $quality);
        }

        return null;
    }

    /**
     * Cuando GD no tiene soporte WebP, guardamos JPEG para no bloquear el registro.
     *
     * @param  array<string, mixed>  $filenames
     * @return array<string, string>
     */
    private function resolveTargetFilenames(array $filenames): array
    {
        $resolved = [];

        foreach (['full', 'medium', 'thumb'] as $key) {
            $baseName = (string) ($filenames[$key] ?? match ($key) {
                'medium' => 'avatar_medium.webp',
                'thumb' => 'avatar_thumb.webp',
                default => 'avatar.webp',
            });

            $resolved[$key] = $this->canEncodeWebp()
                ? $baseName
                : $this->replaceExtension($baseName, 'jpg');
        }

        return $resolved;
    }

    private function canEncodeWebp(): bool
    {
        $driver = (string) config('image.driver', '');
        $isGdDriver = str_contains(strtolower($driver), 'drivers\\gd\\driver');

        if ($isGdDriver) {
            return function_exists('imagewebp');
        }

        return true;
    }

    private function canEncodeJpeg(): bool
    {
        $driver = (string) config('image.driver', '');
        $isGdDriver = str_contains(strtolower($driver), 'drivers\\gd\\driver');

        if ($isGdDriver) {
            return function_exists('imagejpeg');
        }

        return true;
    }

    private function canProcessProfileVariants(): bool
    {
        return $this->canEncodeWebp() || $this->canEncodeJpeg();
    }

    private function storeOriginalUploadedFile(UploadedFile $file, string $binary, string $directory): string
    {
        $directory = trim($directory, '/');
        $disk = Storage::disk('public');
        $disk->makeDirectory($directory);

        $extension = strtolower($file->getClientOriginalExtension());
        if ($extension === '') {
            $extension = strtolower($file->extension());
        }

        if ($extension === '') {
            $extension = 'jpg';
        }

        $filename = 'avatar_original.'.$extension;
        $path = ($directory !== '' ? $directory.'/' : '').$filename;
        $disk->put($path, $binary);

        return $path;
    }

    private function replaceExtension(string $filename, string $newExtension): string
    {
        $filename = trim($filename);
        if ($filename === '') {
            return $filename;
        }

        $pathInfo = pathinfo($filename);
        $name = (string) ($pathInfo['filename'] ?? $filename);

        return $name.'.'.$newExtension;
    }
}
