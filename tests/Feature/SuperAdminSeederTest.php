<?php

use App\Models\AdminRole;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

it('creates a configured super-admin in production and preserves its password on reruns', function () {
    config([
        'admin.super_admin' => [
            'name' => 'Production Admin',
            'username' => 'production-admin',
            'email' => 'admin@example.com',
            'password' => 'a-secure-password',
        ],
    ]);
    $this->app['env'] = 'production';

    $this->app->make(DatabaseSeeder::class)->run();

    $user = User::query()->where('email', 'admin@example.com')->firstOrFail();
    $passwordHash = $user->password;

    expect($user->name)->toBe('Production Admin')
        ->and($user->is_admin)->toBeTrue()
        ->and($user->admin_role)->toBe('super_admin')
        ->and($user->role_id)->toBe(AdminRole::query()->where('type', 'super_admin')->value('id'))
        ->and(Hash::check('a-secure-password', $passwordHash))->toBeTrue();

    $this->app->make(DatabaseSeeder::class)->run();

    expect(User::query()->where('email', 'admin@example.com')->count())->toBe(1)
        ->and($user->fresh()->password)->toBe($passwordHash);
});

it('does not create the bootstrap user outside production', function () {
    config([
        'admin.super_admin' => [],
    ]);
    $this->app['env'] = 'testing';

    $this->app->make(DatabaseSeeder::class)->run();

    expect(User::query()->count())->toBe(0);
});
