<?php

namespace Database\Seeders;

use App\Models\AdminRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use RuntimeException;

class SuperAdminSeeder extends Seeder
{
    /**
     * Seed the production super-admin account.
     */
    public function run(): void
    {
        if (! app()->isProduction()) {
            return;
        }

        $settings = config('admin.super_admin', []);

        foreach (['name', 'username', 'email', 'password'] as $key) {
            if (! is_string($settings[$key] ?? null) || trim($settings[$key]) === '') {
                throw new RuntimeException('Set SUPER_ADMIN_NAME, SUPER_ADMIN_USERNAME, SUPER_ADMIN_EMAIL, and SUPER_ADMIN_PASSWORD before seeding production.');
            }
        }

        if (strlen($settings['password']) < 8) {
            throw new RuntimeException('SUPER_ADMIN_PASSWORD must contain at least 8 characters.');
        }

        $role = AdminRole::query()->where('type', 'super_admin')->firstOrFail();
        $user = User::query()->firstOrCreate(
            ['email' => trim($settings['email'])],
            [
                'name' => trim($settings['name']),
                'username' => trim($settings['username']),
                'password' => $settings['password'],
            ],
        );

        $user->forceFill([
            'is_admin' => true,
            'admin_role' => 'super_admin',
            'role_id' => $role->id,
        ])->save();
    }
}
