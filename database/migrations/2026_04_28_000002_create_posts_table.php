<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('country_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('story');
            $table->string('food_label');
            $table->string('drink_label');
            $table->string('experience_type');
            $table->string('drink_type');
            $table->timestamps();

            $table->index(['country_id', 'created_at']);
            $table->index(['experience_type', 'drink_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
