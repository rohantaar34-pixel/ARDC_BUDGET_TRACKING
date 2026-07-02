<?php
// database/migrations/2026_06_10_000001_create_banned_ips_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('banned_ips', function (Blueprint $table) {
            $table->id();
            $table->string('ip_address', 45)->unique();
            $table->integer('attempt_count')->default(5);
            $table->string('reason')->default('Too many failed login attempts');
            $table->timestamp('banned_at')->useCurrent();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('banned_ips');
    }
};