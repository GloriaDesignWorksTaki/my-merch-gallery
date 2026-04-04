<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('merch_item_likes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('merch_item_id')->constrained('merch_items')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['merch_item_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('merch_item_likes');
    }
};
