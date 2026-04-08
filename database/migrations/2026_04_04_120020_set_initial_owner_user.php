<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const OWNER_EMAILS = [
        'dropup25.yuya@gmail.com',
        'gloriadesignworks@gmail.com',
    ];

    public function up(): void
    {
        DB::table('users')
            ->whereIn('email', self::OWNER_EMAILS)
            ->update(['role' => 'owner']);
    }

    public function down(): void
    {
        DB::table('users')
            ->whereIn('email', self::OWNER_EMAILS)
            ->where('role', 'owner')
            ->update(['role' => 'user']);
    }
};
