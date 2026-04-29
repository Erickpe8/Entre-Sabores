<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Optimiza consultas frecuentes: seguidores por usuario (following_id), posts por etiqueta (tag_id),
 * y correlaciones / conteos de likes por post y por usuario.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('follows', function (Blueprint $table) {
            $table->index('following_id');
        });

        Schema::table('post_tag', function (Blueprint $table) {
            $table->index('tag_id');
        });

        Schema::table('likes', function (Blueprint $table) {
            $table->index(['user_id', 'post_id']);
        });
    }

    public function down(): void
    {
        Schema::table('likes', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'post_id']);
        });

        Schema::table('post_tag', function (Blueprint $table) {
            $table->dropIndex(['tag_id']);
        });

        Schema::table('follows', function (Blueprint $table) {
            $table->dropIndex(['following_id']);
        });
    }
};
