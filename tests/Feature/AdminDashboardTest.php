<?php

use App\Models\AdminRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('renders a records administrator dashboard with master-list actions', function () {
    $admin = User::factory()->create();
    $admin->forceFill(['is_admin' => true, 'admin_role' => 'records_admin'])->save();

    $this->actingAs($admin)
        ->get('/dashboard')
        ->assertRedirectToRoute('filament.admin.pages.dashboard');

    $this->get('/admin')
        ->assertOk()
        ->assertSee('Graduate records')
        ->assertSee('Accounts claimed')
        ->assertSee('Open master list')
        ->assertDontSee('Open content manager');
});

it('renders a content administrator dashboard with publishing and application actions', function () {
    $admin = User::factory()->create();
    $admin->forceFill(['is_admin' => true, 'admin_role' => 'content_admin'])->save();

    $this->actingAs($admin)
        ->get('/dashboard')
        ->assertRedirectToRoute('filament.admin.pages.dashboard');

    $this->get('/admin')
        ->assertOk()
        ->assertSee('Open surveys')
        ->assertSee('Active jobs')
        ->assertSee('Open content manager')
        ->assertDontSee('Administrator access');
});

it('renders a super administrator dashboard with access to every module', function () {
    $admin = User::factory()->create();
    $admin->forceFill(['is_admin' => true, 'admin_role' => 'super_admin'])->save();

    $this->actingAs($admin)
        ->get('/dashboard')
        ->assertRedirectToRoute('filament.admin.pages.dashboard');

    $this->get('/admin')
        ->assertOk()
        ->assertSee('Graduate records')
        ->assertSee('Open surveys')
        ->assertSee('Active jobs')
        ->assertSee('Administrator access')
        ->assertSee('Open master list')
        ->assertSee('Open content manager');
});

it('allows administrators with a role to access the Filament panel', function () {
    $role = AdminRole::factory()->create();
    $admin = User::factory()->create();
    $admin->forceFill(['is_admin' => true, 'role_id' => $role->id])->save();

    $this->actingAs($admin)
        ->get('/admin')
        ->assertOk();
});

it('forbids alumni and administrators without a role from the Filament panel', function () {
    $alumnus = User::factory()->create();
    $adminWithoutRole = User::factory()->create();
    $adminWithoutRole->forceFill(['is_admin' => true, 'admin_role' => 'missing_role'])->save();

    $this->actingAs($alumnus)
        ->get('/admin')
        ->assertForbidden();

    $this->actingAs($adminWithoutRole)
        ->get('/admin')
        ->assertForbidden();
});
