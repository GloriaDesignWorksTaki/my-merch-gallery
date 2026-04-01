<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  public function up(): void
  {
    Schema::table('bands', function (Blueprint $table) {
      $table->string('musicbrainz_id')->nullable()->unique()->after('created_by');
    });
  }

  public function down(): void
  {
    Schema::table('bands', function (Blueprint $table) {
      $table->dropUnique(['musicbrainz_id']);
      $table->dropColumn('musicbrainz_id');
    });
  }
};
