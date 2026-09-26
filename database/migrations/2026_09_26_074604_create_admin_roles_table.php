<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('admin_roles', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 120);
            $table->string('type', 60)->unique();
            $table->text('description')->nullable();
            $table->json('permissions');
            $table->boolean('is_system')->default(false);
            $table->timestamps();
        });

        $now = now();
        DB::table('admin_roles')->insert([
            [
                'name' => 'Super administrator',
                'type' => 'super_admin',
                'description' => 'Manage every module and administrator role.',
                'permissions' => json_encode(['*']),
                'is_system' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Content administrator',
                'type' => 'content_admin',
                'description' => 'Manage announcements, surveys, jobs, and applications.',
                'permissions' => json_encode(['announcements', 'surveys', 'jobs']),
                'is_system' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Records administrator',
                'type' => 'records_admin',
                'description' => 'Manage graduate and applicant records.',
                'permissions' => json_encode(['graduates']),
                'is_system' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);

        Schema::table('users', function (Blueprint $table): void {
            $table->foreignId('role_id')->nullable()->after('admin_role')->constrained('admin_roles')->restrictOnDelete();
        });

        $roleIds = DB::table('admin_roles')->pluck('id', 'type');

        DB::table('users')->where('is_admin', true)->get(['id', 'admin_role'])->each(function (object $admin) use ($roleIds): void {
            $roleId = $roleIds[$admin->admin_role] ?? $roleIds['super_admin'];
            DB::table('users')->where('id', $admin->id)->update(['role_id' => $roleId]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('role_id');
        });

        Schema::dropIfExists('admin_roles');
    }
};
