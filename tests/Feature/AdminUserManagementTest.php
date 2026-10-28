<?php

use App\Models\AdminRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

it('lets a super admin create an administrator account with a role', function () {
    $superAdmin = User::factory()->create();
    $superAdmin->forceFill(['is_admin' => true])->save();
    $recordsRole = AdminRole::query()->where('type', 'records_admin')->firstOrFail();

    $this->actingAs($superAdmin)
        ->get('/admin/users')
        ->assertOk()
        ->assertSee('Add Administrator')
        ->assertDontSee('Temporary password');

    $this->actingAs($superAdmin)
        ->get('/admin/users/create')
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
            'role_id' => $recordsRole->id,
        ])
        ->assertRedirectToRoute('admin.users.index');

    $this->assertDatabaseHas('users', [
        'name' => 'Casey Admin',
        'username' => 'caseyadmin',
        'email' => 'casey.admin@example.com',
        'is_admin' => true,
        'admin_role' => 'records_admin',
        'role_id' => $recordsRole->id,
    ]);
    expect(Hash::check('a-secure-password', User::query()->where('email', 'casey.admin@example.com')->value('password')))->toBeTrue();
});

it('filters administrator accounts by search and assigned role', function () {
    $superAdmin = User::factory()->create();
    $superAdmin->forceFill(['is_admin' => true])->save();
    $recordsRole = AdminRole::query()->where('type', 'records_admin')->firstOrFail();
    $contentRole = AdminRole::query()->where('type', 'content_admin')->firstOrFail();
    $recordsUser = User::factory()->create(['name' => 'Casey Records', 'email' => 'casey@example.com']);
    $recordsUser->forceFill(['is_admin' => true, 'role_id' => $recordsRole->id])->save();
    $contentUser = User::factory()->create(['name' => 'Jordan Content', 'email' => 'jordan@example.com']);
    $contentUser->forceFill(['is_admin' => true, 'role_id' => $contentRole->id])->save();

    $this->actingAs($superAdmin)
        ->get('/admin/users?search=casey&role='.$recordsRole->id)
        ->assertOk()
        ->assertSee('Casey Records')
        ->assertDontSee('Jordan Content');
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
        ->patch("/admin/users/{$recordsAdmin->id}", ['role_id' => AdminRole::query()->where('type', 'content_admin')->value('id')])
        ->assertRedirectToRoute('admin.users.index');

    expect($recordsAdmin->fresh()->admin_role)->toBe('content_admin');

    $this->patch("/admin/users/{$superAdmin->id}", ['role' => 'records_admin'])
        ->assertForbidden();

    expect($superAdmin->fresh()->admin_role)->toBe('super_admin');
});

it('lets a super admin edit another administrator account', function () {
    $superAdmin = User::factory()->create();
    $superAdmin->forceFill(['is_admin' => true, 'admin_role' => 'super_admin'])->save();
    $recordsAdmin = User::factory()->create();
    $recordsAdmin->forceFill(['is_admin' => true, 'admin_role' => 'records_admin'])->save();

    $this->actingAs($superAdmin)
        ->get('/admin/users')
        ->assertSee('data-admin-edit-open', false)
        ->assertSee('id="admin-edit-dialog"', false)
        ->assertSee('Administrator name')
        ->assertSee('Email address')
        ->assertSee('Delete')
        ->assertSee('Open account menu');

    $this->actingAs($superAdmin)
        ->patch("/admin/users/{$recordsAdmin->id}", [
            'name' => 'Casey Updated',
            'username' => 'caseyupdated',
            'email' => 'casey.updated@example.com',
            'role_id' => AdminRole::query()->where('type', 'content_admin')->value('id'),
        ])
        ->assertRedirectToRoute('admin.users.index');

    $this->assertDatabaseHas('users', [
        'id' => $recordsAdmin->id,
        'name' => 'Casey Updated',
        'username' => 'caseyupdated',
        'email' => 'casey.updated@example.com',
        'admin_role' => 'content_admin',
    ]);
});

it('lets a super admin delete another administrator but not their own account', function () {
    $superAdmin = User::factory()->create();
    $superAdmin->forceFill(['is_admin' => true, 'admin_role' => 'super_admin'])->save();
    $recordsAdmin = User::factory()->create();
    $recordsAdmin->forceFill(['is_admin' => true, 'admin_role' => 'records_admin'])->save();

    $this->actingAs($superAdmin)
        ->delete("/admin/users/{$recordsAdmin->id}")
        ->assertRedirectToRoute('admin.users.index');

    $this->assertDatabaseMissing('users', ['id' => $recordsAdmin->id]);

    $this->delete("/admin/users/{$superAdmin->id}")->assertForbidden();
    $this->assertDatabaseHas('users', ['id' => $superAdmin->id]);
});

it('does not allow deleting the last administrator with role-management access', function () {
    $accountManagerRole = AdminRole::query()->create([
        'name' => 'Account manager',
        'type' => 'account_manager',
        'permissions' => ['manage_admins'],
    ]);
    $roleManagerRole = AdminRole::query()->create([
        'name' => 'Role manager',
        'type' => 'role_manager',
        'permissions' => ['manage_roles'],
    ]);
    $accountManager = User::factory()->create();
    $accountManager->forceFill(['is_admin' => true, 'role_id' => $accountManagerRole->id])->save();
    $roleManager = User::factory()->create();
    $roleManager->forceFill(['is_admin' => true, 'role_id' => $roleManagerRole->id])->save();

    $this->actingAs($accountManager)
        ->delete("/admin/users/{$roleManager->id}")
        ->assertSessionHasErrors('admin');

    $this->assertDatabaseHas('users', ['id' => $roleManager->id]);
});

it('keeps the acting super administrator in place when another super administrator is reassigned', function () {
    $superAdmin = User::factory()->create();
    $superAdmin->forceFill(['is_admin' => true, 'admin_role' => 'super_admin'])->save();
    $otherSuperAdmin = User::factory()->create();
    $otherSuperAdmin->forceFill(['is_admin' => true, 'admin_role' => 'super_admin'])->save();

    $this->actingAs($superAdmin)
        ->patch("/admin/users/{$otherSuperAdmin->id}", ['role_id' => AdminRole::query()->where('type', 'records_admin')->value('id')])
        ->assertRedirectToRoute('admin.users.index');

    expect($otherSuperAdmin->fresh()->admin_role)->toBe('records_admin');
    expect($superAdmin->fresh()->admin_role)->toBe('super_admin');
});
