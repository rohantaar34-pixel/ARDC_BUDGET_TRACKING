<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('access_roles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('workflow_type');
            $table->json('module_permissions');
            $table->boolean('is_protected')->default(false);
            $table->timestamps();
        });

        $now = now();
        $roles = [
            'admin' => [
                'name' => 'Super Admin',
                'workflow_type' => 'administrator',
                'module_permissions' => [
                    'ledger',
                    'documents',
                    'monitoring_review',
                    'inventory',
                    'material_approvals',
                    'settings_projects',
                    'settings_users',
                ],
                'is_protected' => true,
            ],
            'office_engineer' => [
                'name' => 'Office Engineer',
                'workflow_type' => 'reviewer',
                'module_permissions' => [
                    'documents',
                    'monitoring_review',
                    'inventory',
                    'material_approvals',
                ],
                'is_protected' => false,
            ],
            'employee' => [
                'name' => 'Site Engineer',
                'workflow_type' => 'field_staff',
                'module_permissions' => [
                    'monitoring_submit',
                    'material_requests',
                ],
                'is_protected' => false,
            ],
        ];

        foreach ($roles as $legacyRole => $role) {
            $roleId = DB::table('access_roles')->insertGetId([
                'name' => $role['name'],
                'slug' => Str::slug($role['name']),
                'description' => 'Default role migrated from the original access configuration.',
                'workflow_type' => $role['workflow_type'],
                'module_permissions' => json_encode($role['module_permissions']),
                'is_protected' => $role['is_protected'],
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $roles[$legacyRole]['id'] = $roleId;
        }

        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('access_role_id')
                ->nullable()
                ->after('role')
                ->constrained('access_roles')
                ->nullOnDelete();
        });

        foreach ($roles as $legacyRole => $role) {
            DB::table('users')
                ->where('role', $legacyRole)
                ->update(['access_role_id' => $role['id']]);
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('access_role_id');
        });

        Schema::dropIfExists('access_roles');
    }
};
