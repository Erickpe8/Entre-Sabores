<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Repara desviaciones entre notificaciones no leídas en BD y users.unread_notifications_count.
 */
class SyncUnreadNotificationCountsCommand extends Command
{
    protected $signature = 'notifications:sync-unread-counts {--chunk=200 : Filas por lote}';

    protected $description = 'Recalcula users.unread_notifications_count desde la tabla notifications';

    public function handle(): int
    {
        $chunk = max(1, (int) $this->option('chunk'));
        $userClass = User::class;
        $updated = 0;

        DB::table('users')->orderBy('id')->chunkById($chunk, function ($rows) use ($userClass, &$updated): void {
            foreach ($rows as $row) {
                $count = DB::table('notifications')
                    ->where('notifiable_type', $userClass)
                    ->where('notifiable_id', $row->id)
                    ->whereNull('read_at')
                    ->count();

                DB::table('users')->where('id', $row->id)->update([
                    'unread_notifications_count' => $count,
                ]);
                $updated++;
            }
        });

        $this->info("Usuarios procesados: {$updated}");

        return self::SUCCESS;
    }
}
