<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('merch_items', function (Blueprint $table) {
            $table->string('size_note', 255)->nullable()->after('era_label');
        });
    }

    public function down(): void
    {
        Schema::table('merch_items', function (Blueprint $table) {
            $table->dropColumn('size_note');
        });
    }
};
