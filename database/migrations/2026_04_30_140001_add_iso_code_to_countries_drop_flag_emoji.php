<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('countries', function (Blueprint $table) {
            $table->string('iso_code', 2)->nullable()->after('slug');
        });

        foreach (\Illuminate\Support\Facades\DB::table('countries')->orderBy('id')->get() as $row) {
            $iso = match ($row->slug) {
                'colombia' => 'CO',
                'mexico' => 'MX',
                'argentina' => 'AR',
                'espana' => 'ES',
                default => null,
            };
            if ($iso !== null) {
                \Illuminate\Support\Facades\DB::table('countries')->where('id', $row->id)->update(['iso_code' => $iso]);
            }
        }

        Schema::table('countries', function (Blueprint $table) {
            $table->dropColumn('flag_emoji');
        });
    }

    public function down(): void
    {
        Schema::table('countries', function (Blueprint $table) {
            $table->string('flag_emoji', 8)->default('');
        });

        Schema::table('countries', function (Blueprint $table) {
            $table->dropColumn('iso_code');
        });
    }
};
