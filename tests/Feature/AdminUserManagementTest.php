<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

it('lets a super admin create an administrator account with a role', function () {
    $superAdmin = User::factory()->create();
    $superAdmin->forceFill(['is_admin' => true])->save();

    $this->actingAs($superAdmin)
        ->get('/admin/users')
        ->assertOk()
        ->assertSee('Create an administrator')
        ->assertSee('Records administrator');

    $this->actingAs($superAdmin)
        ->post('/admin/users', [
            'name' => 'Casey Admin',
            'username' => 'caseyadmin',
            'email' => 'casey.admin@example.com',
            'password' => 'a-secure-password',
            'password_confirmation' => 'a-secure-password',
            'role' => 'records_admin',
        ])
        ->assertRedirectToRoute('admin.users.index');

    $this->assertDatabaseHas('users', [
        'name' => 'Casey Admin',
        'username' => 'caseyadmin',
        'email' => 'casey.admin@example.com',
        'is_admin' => true,
        'admin_role' => 'records_admin',
    ]);
    expect(Hash::check('a-secure-password', User::query()->where('email', 'casey.admin@example.com')->value('password')))->toBeTrue();
});

it('limits administrators to modules granted by their role', function () {
    $recordsAdmin = User::factory()->create();
    $recordsAdmin->forceFill(['is_admin' => true, 'admin_role' => 'records_admin'])->save();
    $contentAdmin = User::factory()->create();
    $contentAdmin->forceFill(['is_admin' => true, 'admin_role' => 'content_admin'])->save();

    $this->actingAs($recordsAdmin)
        ->get('/admin/graduates')
        ->assertOk();
    $this->actingAs($recordsAdmin)
        ->get('/admin/announcements')
        ->assertForbidden();
    $this->actingAs($contentAdmin)
        ->get('/admin/announcements')
        ->assertOk();
    $this->actingAs($contentAdmin)
        ->get('/admin/graduates')
        ->assertForbidden();
    $this->actingAs($contentAdmin)
        ->get('/admin/users')
        ->assertForbidden();
});

it('does not allow an administrator to assign roles', function () {
    $contentAdmin = User::factory()->create();
    $contentAdmin->forceFill(['is_admin' => true, 'admin_role' => 'content_admin'])->save();

    $this->actingAs($contentAdmin)
        ->post('/admin/users', [
            'name' => 'New Admin',
            'username' => 'newadmin',
            'email' => 'newadmin@example.com',
            'password' => 'a-secure-password',
            'password_confirmation' => 'a-secure-password',
            'role' => 'records_admin',
        ])
        ->assertForbidden();

    $this->assertDatabaseMissing('users', ['email' => 'newadmin@example.com']);
});

it('lets a super admin change another administrator role but not their own', function () {
    $superAdmin = User::factory()->create();
    $superAdmin->forceFill(['is_admin' => true, 'admin_role' => 'super_admin'])->save();
    $recordsAdmin = User::factory()->create();
    $recordsAdmin->forceFill(['is_admin' => true, 'admin_role' => 'records_admin'])->save();

    $this->actingAs($superAdmin)
        ->patch("/admin/users/{$recordsAdmin->id}", ['role' => 'content_admin'])
        ->assertRedirectToRoute('admin.users.index');

    expect($recordsAdmin->fresh()->admin_role)->toBe('content_admin');

    $this->patch("/admin/users/{$superAdmin->id}", ['role' => 'records_admin'])
        ->assertForbidden();

    expect($superAdmin->fresh()->admin_role)->toBe('super_admin');
});

it('keeps the acting super administrator in place when another super administrator is reassigned', function () {
    $superAdmin = User::factory()->create();
    $superAdmin->forceFill(['is_admin' => true, 'admin_role' => 'super_admin'])->save();
    $otherSuperAdmin = User::factory()->create();
    $otherSuperAdmin->forceFill(['is_admin' => true, 'admin_role' => 'super_admin'])->save();

    $this->actingAs($superAdmin)
        ->patch("/admin/users/{$otherSuperAdmin->id}", ['role' => 'records_admin'])
        ->assertRedirectToRoute('admin.users.index');

    expect($otherSuperAdmin->fresh()->admin_role)->toBe('records_admin');
    expect($superAdmin->fresh()->admin_role)->toBe('super_admin');
});
