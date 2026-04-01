<?php

use App\Models\Band;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  public function up(): void
  {
    Schema::table('bands', function (Blueprint $table) {
      $table->string('sort_name')->nullable()->after('slug')->index();
    });

    DB::table('bands')
      ->select(['id', 'name'])
      ->orderBy('id')
      ->lazy()
      ->each(function (object $band): void {
        DB::table('bands')
          ->where('id', $band->id)
          ->update(['sort_name' => Band::normalizeSortName($band->name)]);
      });
  }

  public function down(): void
  {
    Schema::table('bands', function (Blueprint $table) {
      $table->dropColumn('sort_name');
    });
  }
};
