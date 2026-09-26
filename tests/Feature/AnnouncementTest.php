<?php

use App\Models\Announcement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('shows the announcement manager to administrators', function () {
    $admin = User::factory()->create();
    $admin->forceFill(['is_admin' => true])->save();

    $this->actingAs($admin)
        ->get('/admin/announcements')
        ->assertOk()
        ->assertSee('Announcements');
});

it('forbids alumni from creating announcements', function () {
    $alumni = User::factory()->create();

    $this->actingAs($alumni)
        ->post('/admin/announcements', [
            'title' => 'Campus update',
            'body' => 'The library will reopen next week.',
            'status' => 'published',
        ])
        ->assertForbidden();

    $this->assertDatabaseCount('announcements', 0);
});

it('lets administrators publish announcements from the manager', function () {
    $admin = User::factory()->create();
    $admin->forceFill(['is_admin' => true])->save();

    $response = $this->actingAs($admin)->post('/admin/announcements', [
        'title' => 'Campus update',
        'body' => 'The library will reopen next week.',
        'status' => 'published',
    ]);

    $response->assertRedirectToRoute('admin.announcements.index')
        ->assertSessionHas('status', 'Announcement created.');
    $this->assertDatabaseHas('announcements', [
        'title' => 'Campus update',
        'body' => 'The library will reopen next week.',
        'created_by' => $admin->id,
    ]);
    expect(Announcement::query()->firstOrFail()->published_at)->not->toBeNull();
});

it('shows published announcements but not drafts on the alumni dashboard', function () {
    $published = Announcement::query()->create([
        'title' => 'Campus update',
        'body' => 'The library will reopen next week.',
    ]);
    $published->forceFill(['published_at' => now()])->save();
    Announcement::query()->create([
        'title' => 'Planning note',
        'body' => 'This message is not ready for alumni.',
    ]);
    $scheduled = Announcement::query()->create([
        'title' => 'Future event',
        'body' => 'This event has not been announced yet.',
    ]);
    $scheduled->forceFill(['published_at' => now()->addDay()])->save();
    $alumni = User::factory()->create();

    $this->actingAs($alumni)
        ->get('/dashboard')
        ->assertOk()
        ->assertSee('Announcements')
        ->assertSee('Campus update')
        ->assertSee('The library will reopen next week.')
        ->assertDontSee('Planning note')
        ->assertDontSee('Future event');
});

it('escapes announcement markup when displaying it on the alumni dashboard', function () {
    $announcement = Announcement::query()->create([
        'title' => 'Web safety note',
        'body' => '<script>alert("x")</script>',
    ]);
    $announcement->forceFill(['published_at' => now()])->save();
    $alumni = User::factory()->create();

    $this->actingAs($alumni)
        ->get('/dashboard')
        ->assertSee('&lt;script&gt;alert(&quot;x&quot;)&lt;/script&gt;', false)
        ->assertDontSee('<script>alert("x")</script>', false);
});

it('lets administrators edit an announcement', function () {
    $admin = User::factory()->create();
    $admin->forceFill(['is_admin' => true])->save();
    $announcement = Announcement::query()->create([
        'title' => 'Career fair',
        'body' => 'Registration opens Monday.',
    ]);

    $this->actingAs($admin)
        ->get("/admin/announcements/{$announcement->id}/edit")
        ->assertOk()
        ->assertSee('Edit announcement');

    $this->actingAs($admin)
        ->patch("/admin/announcements/{$announcement->id}", [
            'title' => 'Career fair updated',
            'body' => 'Registration opens Tuesday morning.',
            'status' => 'published',
        ])
        ->assertRedirectToRoute('admin.announcements.index');

    $this->assertDatabaseHas('announcements', [
        'id' => $announcement->id,
        'title' => 'Career fair updated',
        'body' => 'Registration opens Tuesday morning.',
    ]);
});

it('publishes drafts when an administrator changes their status', function () {
    $admin = User::factory()->create();
    $admin->forceFill(['is_admin' => true])->save();
    $announcement = Announcement::query()->create([
        'title' => 'Career fair',
        'body' => 'Registration opens Monday.',
    ]);

    $this->actingAs($admin)
        ->patch("/admin/announcements/{$announcement->id}", ['status' => 'published'])
        ->assertRedirect();

    expect($announcement->fresh()->published_at)->not->toBeNull();
});
