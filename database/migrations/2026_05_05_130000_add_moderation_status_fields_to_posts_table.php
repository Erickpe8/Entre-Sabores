<?php

use App\Models\Post;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table): void {
            $table->string('status', 20)->default(Post::STATUS_PENDING)->after('drink');
            $table->json('moderation_reason')->nullable()->after('analysis_result');
            $table->softDeletes();
            $table->index(['status', 'analysis_status', 'updated_at']);
        });
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table): void {
            $table->dropIndex(['status', 'analysis_status', 'updated_at']);
            $table->dropSoftDeletes();
            $table->dropColumn(['moderation_reason', 'status']);
        });
    }
};

