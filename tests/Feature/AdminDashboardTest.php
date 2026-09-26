<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('renders a records administrator dashboard with master-list actions', function () {
    $admin = User::factory()->create();
    $admin->forceFill(['is_admin' => true, 'admin_role' => 'records_admin'])->save();

    $this->actingAs($admin)
        ->get('/dashboard')
        ->assertOk()
        ->assertSee('Records administrator dashboard')
        ->assertSee('Graduate records')
        ->assertSee('Applicant profiles')
        ->assertSee('Open master list')
        ->assertDontSee('Announcement manager');
});

it('renders a content administrator dashboard with publishing and application actions', function () {
    $admin = User::factory()->create();
    $admin->forceFill(['is_admin' => true, 'admin_role' => 'content_admin'])->save();

    $this->actingAs($admin)
        ->get('/dashboard')
        ->assertOk()
        ->assertSee('Content administrator dashboard')
        ->assertSee('Published announcements')
        ->assertSee('Active jobs')
        ->assertSee('Open content manager')
        ->assertDontSee('Administrator access');
});

it('renders a super administrator dashboard with access to every module', function () {
    $admin = User::factory()->create();
    $admin->forceFill(['is_admin' => true, 'admin_role' => 'super_admin'])->save();

    $this->actingAs($admin)
        ->get('/dashboard')
        ->assertOk()
        ->assertSee('Super administrator dashboard')
        ->assertSee('Administrator accounts')
        ->assertSee('Graduate records')
        ->assertSee('Published announcements')
        ->assertSee('Administrator access')
        ->assertSee('Open master list')
        ->assertSee('Open content manager');
});
