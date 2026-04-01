<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  public function up(): void
  {
    Schema::create('band_links', function (Blueprint $table) {
      $table->id();
      $table->foreignId('band_id')->constrained('bands')->cascadeOnDelete();
      $table->string('url', 2048);
      $table->unsignedTinyInteger('sort_order')->default(1);
      $table->timestamps();

      $table->unique(['band_id', 'sort_order']);
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('band_links');
  }
};
