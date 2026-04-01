<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  public function up(): void
  {
    Schema::create('merch_items', function (Blueprint $table) {
      $table->id();
      $table->foreignId('band_id')->constrained('bands')->cascadeOnDelete();
      $table->foreignId('merch_category_id')->constrained('merch_categories')->restrictOnDelete();
      $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
      $table->string('name')->index();
      $table->string('slug')->unique();
      $table->text('description')->nullable();
      $table->unsignedSmallInteger('release_year')->nullable()->index();
      $table->string('era_label')->nullable();
      $table->boolean('is_official')->default(true);
      $table->string('source_type')->default('user_created');
      $table->timestamps();
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('merch_items');
  }
};
