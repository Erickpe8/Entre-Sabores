<?php

use App\Models\Post;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table): void {
            $table->text('content')->nullable()->after('description');
            $table->string('food', 120)->nullable()->after('content');
            $table->string('drink', 120)->nullable()->after('food');
            $table->string('analysis_status', 20)->default(Post::ANALYSIS_STATUS_PENDING)->after('ai_analysis');
            $table->json('analysis_result')->nullable()->after('analysis_status');
            $table->index(['analysis_status', 'updated_at']);
        });

        DB::table('posts')->update([
            'content' => DB::raw('description'),
            'analysis_status' => Post::ANALYSIS_STATUS_COMPLETED,
            'analysis_result' => DB::raw('ai_analysis'),
        ]);
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table): void {
            $table->dropIndex(['analysis_status', 'updated_at']);
            $table->dropColumn(['analysis_result', 'analysis_status', 'drink', 'food', 'content']);
        });
    }
};

