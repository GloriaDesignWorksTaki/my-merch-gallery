<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  public function up(): void
  {
    Schema::create('bands', function (Blueprint $table) {
      $table->id();
      $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
      $table->string('name')->index();
      $table->string('slug')->unique();
      $table->string('country_code', 2)->nullable();
      $table->text('description')->nullable();
      $table->unsignedSmallInteger('formed_year')->nullable();
      $table->boolean('is_active')->default(true);
      $table->timestamps();
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('bands');
  }
};
