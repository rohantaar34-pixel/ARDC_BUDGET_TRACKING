<?php
// database/migrations/2026_06_10_000003_seed_super_admin_user.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('users')->insertOrIgnore([
            'name'       => 'Super Admin',
            'email'      => 'super@gmail.com',
            'password'   => Hash::make('GR_123'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        DB::table('users')->where('email', 'super@gmail.com')->delete();
    }
};