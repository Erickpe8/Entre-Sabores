<?php

use App\Models\Tag;
use App\Support\CountryNameIsoMap;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tags', function (Blueprint $table) {
            $table->string('iso_code', 2)->nullable()->after('name');
        });

        foreach (Tag::query()->where('type', Tag::TYPE_COUNTRY)->cursor() as $tag) {
            $iso = CountryNameIsoMap::isoForCountryName($tag->name);
            if ($iso !== null) {
                $tag->forceFill(['iso_code' => $iso])->saveQuietly();
            }
        }
    }

    public function down(): void
    {
        Schema::table('tags', function (Blueprint $table) {
            $table->dropColumn('iso_code');
        });
    }
};
