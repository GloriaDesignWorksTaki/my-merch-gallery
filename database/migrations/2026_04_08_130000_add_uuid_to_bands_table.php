<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bands', function (Blueprint $table) {
            $table->uuid('uuid')->nullable()->after('id');
        });

        DB::table('bands')
            ->select(['id'])
            ->orderBy('id')
            ->chunkById(200, function ($bands): void {
                foreach ($bands as $band) {
                    DB::table('bands')
                        ->where('id', $band->id)
                        ->update(['uuid' => (string) Str::uuid()]);
                }
            });

        Schema::table('bands', function (Blueprint $table) {
            $table->unique('uuid');
        });
    }

    public function down(): void
    {
        Schema::table('bands', function (Blueprint $table) {
            $table->dropUnique(['uuid']);
            $table->dropColumn('uuid');
        });
    }
};
