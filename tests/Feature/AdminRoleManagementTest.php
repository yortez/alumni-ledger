<?php

use App\Models\AdminRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('lets a role manager create a role with a custom type and module permissions', function () {
    $superAdmin = User::factory()->create();
    $superAdmin->forceFill(['is_admin' => true, 'admin_role' => 'super_admin'])->save();

    $this->actingAs($superAdmin)
        ->get('/admin/roles')
        ->assertOk()
        ->assertSee('Role definitions')
        ->assertSee('Create a role')
        ->assertSee('Role type');

    $this->post('/admin/roles', [
        'name' => 'Survey coordinator',
        'type' => 'survey_coordinator',
        'description' => 'Creates surveys and reads responses.',
        'permissions' => ['surveys'],
    ])->assertRedirectToRoute('admin.roles.index');

    $this->assertDatabaseHas('admin_roles', [
        'name' => 'Survey coordinator',
        'type' => 'survey_coordinator',
        'description' => 'Creates surveys and reads responses.',
    ]);
    expect(AdminRole::query()->where('type', 'survey_coordinator')->value('permissions'))->toBe(['surveys']);
});

it('lets a role manager edit a role definition and its permissions', function () {
    $superAdmin = User::factory()->create();
    $superAdmin->forceFill(['is_admin' => true, 'admin_role' => 'super_admin'])->save();
    $role = AdminRole::query()->create([
        'name' => 'Survey coordinator',
        'type' => 'survey_coordinator',
        'description' => 'Creates surveys.',
        'permissions' => ['surveys'],
    ]);
    $assignedAdmin = User::factory()->create();
    $assignedAdmin->forceFill(['is_admin' => true, 'role_id' => $role->id])->save();

    $this->actingAs($superAdmin)
        ->patch("/admin/roles/{$role->id}", [
            'name' => 'Survey and jobs coordinator',
            'type' => 'survey_jobs_coordinator',
            'description' => 'Manages surveys and job postings.',
            'permissions' => ['surveys', 'jobs'],
        ])
        ->assertRedirectToRoute('admin.roles.index');

    $this->assertDatabaseHas('admin_roles', [
        'id' => $role->id,
        'name' => 'Survey and jobs coordinator',
        'type' => 'survey_jobs_coordinator',
        'description' => 'Manages surveys and job postings.',
    ]);
    expect($role->fresh()->permissions)->toBe(['surveys', 'jobs']);

    $this->actingAs($assignedAdmin)
        ->get('/admin/jobs')
        ->assertOk();
    $this->get('/admin/announcements')->assertForbidden();
});

it('lets role managers delete unused custom roles but protects system and assigned roles', function () {
    $superAdmin = User::factory()->create();
    $superAdmin->forceFill(['is_admin' => true, 'admin_role' => 'super_admin'])->save();
    $unusedRole = AdminRole::query()->create([
        'name' => 'Unused role',
        'type' => 'unused_role',
        'permissions' => ['jobs'],
    ]);
    $assignedRole = AdminRole::query()->create([
        'name' => 'Assigned role',
        'type' => 'assigned_role',
        'permissions' => ['graduates'],
    ]);
    $assignedAdmin = User::factory()->create();
    $assignedAdmin->forceFill(['is_admin' => true, 'role_id' => $assignedRole->id])->save();
    $systemRole = AdminRole::query()->where('type', 'super_admin')->firstOrFail();

    $this->actingAs($superAdmin)
        ->delete("/admin/roles/{$unusedRole->id}")
        ->assertRedirectToRoute('admin.roles.index');

    $this->delete("/admin/roles/{$assignedRole->id}")
        ->assertSessionHasErrors('role');
    $this->delete("/admin/roles/{$systemRole->id}")
        ->assertSessionHasErrors('role');

    $this->assertDatabaseMissing('admin_roles', ['id' => $unusedRole->id]);
    $this->assertDatabaseHas('admin_roles', ['id' => $assignedRole->id]);
    $this->assertDatabaseHas('admin_roles', ['id' => $systemRole->id]);
});

it('applies custom role permissions to admin module access', function () {
    $role = AdminRole::query()->create([
        'name' => 'Graduate records editor',
        'type' => 'graduate_records_editor',
        'permissions' => ['graduates'],
    ]);
    $admin = User::factory()->create();
    $admin->forceFill(['is_admin' => true, 'role_id' => $role->id])->save();

    $this->actingAs($admin)
        ->get('/admin/graduates')
        ->assertOk();
    $this->get('/admin/announcements')->assertForbidden();
    $this->get('/admin/roles')->assertForbidden();
});

it('does not let an admin without role-management access change role definitions', function () {
    $role = AdminRole::query()->create([
        'name' => 'Content editor',
        'type' => 'content_editor',
        'permissions' => ['announcements'],
    ]);
    $admin = User::factory()->create();
    $admin->forceFill(['is_admin' => true, 'role_id' => $role->id])->save();

    $this->actingAs($admin)
        ->post('/admin/roles', [
            'name' => 'Unauthorized role',
            'type' => 'unauthorized_role',
            'description' => 'Should not be created.',
            'permissions' => ['jobs'],
        ])
        ->assertForbidden();

    $this->assertDatabaseMissing('admin_roles', ['type' => 'unauthorized_role']);
});

it('does not let role managers grant permissions they do not hold', function () {
    $roleManagerRole = AdminRole::query()->create([
        'name' => 'Role manager',
        'type' => 'role_manager',
        'permissions' => ['manage_roles'],
    ]);
    $roleManager = User::factory()->create();
    $roleManager->forceFill(['is_admin' => true, 'role_id' => $roleManagerRole->id])->save();

    $this->actingAs($roleManager)
        ->post('/admin/roles', [
            'name' => 'Account manager',
            'type' => 'account_manager',
            'description' => 'Manages administrator accounts.',
            'permissions' => ['manage_admins'],
        ])
        ->assertForbidden();

    $this->assertDatabaseMissing('admin_roles', ['type' => 'account_manager']);

    $this->post('/admin/roles', [
        'name' => 'Invalid permission role',
        'type' => 'invalid_permission_role',
        'permissions' => ['unknown_permission'],
    ])->assertSessionHasErrors('permissions.0');

    $this->assertDatabaseMissing('admin_roles', ['type' => 'invalid_permission_role']);
});
