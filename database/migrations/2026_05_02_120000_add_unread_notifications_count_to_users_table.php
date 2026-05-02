<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedInteger('unread_notifications_count')->default(0)->after('preferences');
        });

        $userClass = User::class;

        DB::table('users')->orderBy('id')->chunkById(200, function ($rows) use ($userClass): void {
            foreach ($rows as $row) {
                $count = DB::table('notifications')
                    ->where('notifiable_type', $userClass)
                    ->where('notifiable_id', $row->id)
                    ->whereNull('read_at')
                    ->count();

                DB::table('users')->where('id', $row->id)->update([
                    'unread_notifications_count' => $count,
                ]);
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('unread_notifications_count');
        });
    }
};
