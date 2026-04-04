<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type');
            $table->morphs('notifiable');
            $table->text('data');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });

        Schema::table('merch_item_comments', function (Blueprint $table) {
            $table->foreignId('parent_id')
                ->nullable()
                ->after('merch_item_id')
                ->constrained('merch_item_comments')
                ->cascadeOnDelete();
        });

        Schema::create('merch_item_comment_likes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('merch_item_comment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['merch_item_comment_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('merch_item_comment_likes');

        Schema::table('merch_item_comments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('parent_id');
        });

        Schema::dropIfExists('notifications');
    }
};
