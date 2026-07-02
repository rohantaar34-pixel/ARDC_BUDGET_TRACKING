<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('employee')->after('password');
        });

        Schema::table('projects', function (Blueprint $table) {
            $table->unsignedTinyInteger('completion_percentage')->default(0)->after('status');
        });

        Schema::create('project_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['project_id', 'user_id']);
        });

        Schema::create('project_monitoring_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->text('accomplishment_details');
            $table->unsignedTinyInteger('estimated_completion_percentage');
            $table->string('status')->default('pending');
            $table->text('admin_remarks')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('project_monitoring_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_monitoring_report_id')
                ->constrained('project_monitoring_reports')
                ->cascadeOnDelete();
            $table->string('path');
            $table->string('original_name')->nullable();
            $table->timestamps();
        });

        DB::table('projects')->where('status', 'underdevelopment')->update(['status' => 'not_started']);
        DB::table('users')->where('email', 'super@gmail.com')->update(['role' => 'admin']);

        $firstUser = DB::table('users')->orderBy('id')->first();
        if ($firstUser) {
            DB::table('users')->where('id', $firstUser->id)->update(['role' => 'admin']);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('project_monitoring_photos');
        Schema::dropIfExists('project_monitoring_reports');
        Schema::dropIfExists('project_user');

        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn('completion_percentage');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('role');
        });
    }
};
