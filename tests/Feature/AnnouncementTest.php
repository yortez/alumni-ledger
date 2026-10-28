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
        ->assertSee('Announcements')
        ->assertSee('Post Announcement')
        ->assertDontSee('Announcement Details');
});

it('shows the announcement creation form on its own page', function () {
    $admin = User::factory()->create();
    $admin->forceFill(['is_admin' => true])->save();

    $this->actingAs($admin)
        ->get('/admin/announcements/create')
        ->assertOk()
        ->assertSee('Create announcement')
        ->assertSee('Announcement Details')
        ->assertSee('Publish Settings')
        ->assertSee('Pin this announcement');
});

it('filters announcements by title and category', function () {
    $admin = User::factory()->create();
    $admin->forceFill(['is_admin' => true])->save();
    Announcement::query()->create([
        'title' => 'International group specialist',
        'body' => 'Apply for the upcoming role.',
        'category' => 'career',
    ]);
    Announcement::query()->create([
        'title' => 'Campus closure notice',
        'body' => 'The campus will close Friday.',
        'category' => 'general',
    ]);

    $this->actingAs($admin)
        ->get('/admin/announcements?search=specialist&category=career')
        ->assertOk()
        ->assertSee('International group specialist')
        ->assertDontSee('Campus closure notice')
        ->assertSee('1 announcement');
});

it('forbids alumni from creating announcements', function () {
    $alumni = User::factory()->create();

    $this->actingAs($alumni)
        ->post('/admin/announcements', [
            'title' => 'Campus update',
            'body' => 'The library will reopen next week.',
            'category' => 'general',
            'status' => 'published',
            'is_pinned' => '0',
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
        'category' => 'general',
        'status' => 'published',
        'is_pinned' => '0',
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

it('saves announcement category, schedule, expiry, and pin settings', function () {
    $admin = User::factory()->create();
    $admin->forceFill(['is_admin' => true])->save();
    $publishDate = now()->addDays(2)->toDateString();
    $expiryDate = now()->addDays(5)->toDateString();

    $this->actingAs($admin)
        ->post('/admin/announcements', [
            'title' => 'Upcoming career fair',
            'body' => 'Registration opens soon.',
            'category' => 'career',
            'status' => 'published',
            'publish_date' => $publishDate,
            'expiry_date' => $expiryDate,
            'is_pinned' => '1',
        ])
        ->assertRedirectToRoute('admin.announcements.index');

    $announcement = Announcement::query()->where('title', 'Upcoming career fair')->firstOrFail();

    expect($announcement->category)->toBe('career')
        ->and($announcement->published_at->toDateString())->toBe($publishDate)
        ->and($announcement->expires_at->toDateString())->toBe($expiryDate)
        ->and($announcement->is_pinned)->toBeTrue();
    $this->assertDatabaseCount('notifications', 0);
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

it('omits expired announcements and places pinned announcements first', function () {
    $regular = Announcement::query()->create([
        'title' => 'Regular notice',
        'body' => 'A general update.',
    ]);
    $regular->forceFill(['published_at' => now()->subMinute()])->save();
    $pinned = Announcement::query()->create([
        'title' => 'Pinned notice',
        'body' => 'Important update.',
        'category' => 'urgent',
        'is_pinned' => true,
    ]);
    $pinned->forceFill(['published_at' => now()->subMinute()])->save();
    $expired = Announcement::query()->create([
        'title' => 'Expired notice',
        'body' => 'This should no longer appear.',
        'expires_at' => now()->subDay(),
    ]);
    $expired->forceFill(['published_at' => now()->subDays(2)])->save();
    $alumni = User::factory()->create();

    $response = $this->actingAs($alumni)->get('/dashboard')->assertOk();
    $response->assertSee('Pinned notice')->assertSee('Regular notice')->assertDontSee('Expired notice');
    expect(strpos($response->getContent(), 'Pinned notice'))->toBeLessThan(strpos($response->getContent(), 'Regular notice'));
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
        ->get('/admin/announcements')
        ->assertOk()
        ->assertSee('data-announcement-edit-open', false)
        ->assertSee('id="announcement-edit-dialog"', false);

    $this->actingAs($admin)
        ->get("/admin/announcements/{$announcement->id}/edit")
        ->assertOk()
        ->assertSee('Edit announcement');

    $this->actingAs($admin)
        ->patch("/admin/announcements/{$announcement->id}", [
            'title' => 'Career fair updated',
            'body' => 'Registration opens Tuesday morning.',
            'category' => 'career',
            'status' => 'published',
            'publish_date' => now()->toDateString(),
            'expiry_date' => now()->addDays(3)->toDateString(),
            'is_pinned' => '1',
        ])
        ->assertRedirectToRoute('admin.announcements.index');

    $this->assertDatabaseHas('announcements', [
        'id' => $announcement->id,
        'title' => 'Career fair updated',
        'body' => 'Registration opens Tuesday morning.',
        'category' => 'career',
        'is_pinned' => true,
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
