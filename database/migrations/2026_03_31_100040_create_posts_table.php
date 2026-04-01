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
      $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
      $table->foreignId('band_id')->constrained('bands')->cascadeOnDelete();
      $table->foreignId('merch_item_id')->nullable()->constrained('merch_items')->nullOnDelete();
      $table->text('body');
      $table->string('visibility')->default('public')->index();
      $table->timestamp('published_at')->nullable()->index();
      $table->timestamps();
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('posts');
  }
};
