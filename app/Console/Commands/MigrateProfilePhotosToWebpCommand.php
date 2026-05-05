<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\ProfilePhotoService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class MigrateProfilePhotosToWebpCommand extends Command
{
    protected $signature = 'profile-photos:migrate-webp
                            {--dry-run : Solo listar rutas que se convertirían sin escribir}';

    protected $description = 'Convierte fotos de perfil existentes (jpg/png) a WebP con variantes y actualiza la base de datos.';

    public function handle(ProfilePhotoService $profilePhotos): int
    {
        $dry = (bool) $this->option('dry-run');

        $query = User::query()->whereNotNull('profile_photo');

        $count = 0;
        $skipped = 0;
        $errors = 0;

        $query->orderBy('id')->chunkById(50, function ($users) use ($profilePhotos, $dry, &$count, &$skipped, &$errors): void {
            $disk = Storage::disk('public');
            foreach ($users as $user) {
                $raw = (string) $user->profile_photo;
                $path = User::normalizePublicDiskPath($raw) ?? trim(str_replace('\\', '/', $raw), '/');

                if ($path === '') {
                    $skipped++;

                    continue;
                }

                if (! $disk->exists($path)) {
                    $this->warn("Omitido (no existe en disco): usuario #{$user->id} → {$path}");
                    $skipped++;

                    continue;
                }

                if (preg_match('/\.webp$/i', $path) === 1) {
                    $skipped++;

                    continue;
                }

                if ($dry) {
                    $this->line("[dry-run] #{$user->id} {$path}");
                    $count++;

                    continue;
                }

                try {
                    $newPath = $profilePhotos->convertExistingFileToWebpVariants($path);
                    $user->profile_photo = $newPath;
                    $user->save();
                    $this->info("OK #{$user->id} {$path} → {$newPath}");
                    $count++;
                } catch (\Throwable $e) {
                    $this->error("Fallo #{$user->id} {$path}: {$e->getMessage()}");
                    $errors++;
                }
            }
        });

        $this->info("Listo. Convertidas: {$count}, omitidas: {$skipped}, errores: {$errors}.");

        return $errors > 0 ? self::FAILURE : self::SUCCESS;
    }
}
