<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedTinyInteger('avatar_focus_x')->default(50)->after('avatar_path');
            $table->unsignedTinyInteger('avatar_focus_y')->default(50)->after('avatar_focus_x');
            $table->decimal('avatar_zoom', 4, 2)->default(1)->after('avatar_focus_y');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['avatar_focus_x', 'avatar_focus_y', 'avatar_zoom']);
        });
    }
};
