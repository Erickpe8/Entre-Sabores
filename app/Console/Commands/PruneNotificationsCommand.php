<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class PruneNotificationsCommand extends Command
{
    protected $signature = 'notifications:prune
                            {--days=90 : Eliminar notificaciones leídas más antiguas que estos días}
                            {--dry-run : Solo mostrar cuántas filas se borrarían}';

    protected $description = 'Elimina notificaciones de base de datos ya leídas y antiguas (reduce tamaño de tabla).';

    public function handle(): int
    {
        $days = max(30, (int) $this->option('days'));
        $cutoff = now()->subDays($days);

        $query = DB::table('notifications')
            ->whereNotNull('read_at')
            ->where('read_at', '<', $cutoff);

        if ($this->option('dry-run')) {
            $count = (clone $query)->count();
            $this->info("Se eliminarían {$count} notificación(es) leídas anteriores a {$cutoff->toIso8601String()}.");

            return self::SUCCESS;
        }

        $deleted = $query->delete();
        $this->info("Eliminadas {$deleted} notificación(es) leídas antiguas (cutoff: {$days} días).");

        return self::SUCCESS;
    }
}
