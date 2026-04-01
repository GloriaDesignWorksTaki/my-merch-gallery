<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  public function up(): void
  {
    Schema::table('bands', function (Blueprint $table) {
      $table->foreignId('country_id')->nullable()->after('slug')->constrained('countries')->nullOnDelete();
      $table->dropColumn('country_code');
    });
  }

  public function down(): void
  {
    Schema::table('bands', function (Blueprint $table) {
      $table->string('country_code', 2)->nullable()->after('slug');
      $table->dropConstrainedForeignId('country_id');
    });
  }
};
