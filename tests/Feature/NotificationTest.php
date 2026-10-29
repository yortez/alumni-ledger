<?php

use App\Models\Graduate;
use App\Models\Job;
use App\Models\JobApplication;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

it('sends a published announcement as a notification to each user', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $alumni = User::factory()->create();

    $this->actingAs($admin)
        ->post('/admin/announcements', [
            'title' => 'Spring reunion',
            'body' => 'Join us for the annual alumni dinner.',
            'category' => 'events',
            'status' => 'published',
            'is_pinned' => '0',
        ])
        ->assertRedirectToRoute('admin.announcements.index');

    $this->assertDatabaseHas('notifications', [
        'notifiable_id' => $alumni->id,
        'notifiable_type' => User::class,
    ]);

    $this->actingAs($alumni)
        ->get('/dashboard')
        ->assertSee('data-notifications-open', false)
        ->assertSee('id="notification-dialog"', false)
        ->assertSee('Spring reunion')
        ->assertSee('View all notifications');

    $this->actingAs($alumni)
        ->get('/notifications')
        ->assertOk()
        ->assertSee('Spring reunion')
        ->assertSee('Notifications');
});

it('notifies eligible alumni when a survey is published', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    Graduate::query()->create([
        'student_number' => '2022-1101',
        'first_name' => 'Jordan',
        'last_name' => 'Lee',
        'program_code' => 'CS',
        'batch_year' => 2022,
    ]);
    $eligibleAlumni = User::factory()->create([
        'student_number' => '2022-1101',
        'name' => 'Jordan Lee',
    ]);

    $this->actingAs($admin)
        ->post('/admin/surveys', [
            'title' => 'Graduate outcomes survey',
            'description' => 'Tell us about your post-grad path.',
            'questions_text' => 'What is your current role?',
            'target_programs' => ['CS'],
            'target_graduation_years' => ['2022'],
            'status' => 'active',
        ])
        ->assertRedirectToRoute('admin.surveys.index');

    $this->assertDatabaseHas('notifications', [
        'notifiable_id' => $eligibleAlumni->id,
        'notifiable_type' => User::class,
    ]);

    $this->actingAs($eligibleAlumni)
        ->get('/notifications')
        ->assertOk()
        ->assertSee('Graduate outcomes survey');
});

it('notifies the applicant when the job application status changes', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $applicant = User::factory()->create(['name' => 'Riley Moore']);
    $job = Job::factory()->for($admin, 'creator')->create(['title' => 'Research Coordinator']);
    $application = JobApplication::factory()->for($job, 'job')->for($applicant, 'user')->create([
        'status' => 'submitted',
    ]);

    $this->actingAs($admin)
        ->patch(route('admin.jobs.applications.update', [$job, $application]), [
            'status' => 'interview_scheduled',
        ])
        ->assertRedirect(route('admin.jobs.applications.index', $job));

    $this->assertDatabaseHas('notifications', [
        'notifiable_id' => $applicant->id,
        'notifiable_type' => User::class,
    ]);

    $this->actingAs($applicant)
        ->get('/notifications')
        ->assertOk()
        ->assertSee('Interview scheduled');
});

it('keeps notifications unread while the inbox is open and marks one on demand', function () {
    $alumni = User::factory()->create();

    $notification = $alumni->notifications()->create([
        'id' => (string) Str::uuid(),
        'type' => 'database',
        'data' => [
            'type' => 'survey',
            'title' => 'Career mobility survey',
            'message' => 'Five questions about your current role.',
            'survey_id' => 1,
        ],
    ]);

    $this->actingAs($alumni)
        ->get('/notifications')
        ->assertOk()
        ->assertSee('Career mobility survey')
        ->assertSee('data-emphasis="true"', false)
        ->assertSee('1 unread');

    expect($notification->fresh()->read_at)->toBeNull();

    $this->actingAs($alumni)
        ->post(route('notifications.read', $notification->id))
        ->assertOk()
        ->assertJson(['unread' => 0]);

    expect($notification->fresh()->read_at)->not->toBeNull();
});

it('filters the inbox by notification type', function () {
    $alumni = User::factory()->create();

    $alumni->notifications()->create([
        'id' => (string) Str::uuid(),
        'type' => 'database',
        'data' => ['type' => 'announcement', 'title' => 'Transcript requests now online', 'message' => 'The registrar now accepts requests online.'],
    ]);

    $alumni->notifications()->create([
        'id' => (string) Str::uuid(),
        'type' => 'database',
        'data' => ['type' => 'survey', 'title' => 'Career mobility survey', 'message' => 'Five questions about your current role.'],
    ]);

    $filtered = $this->actingAs($alumni)
        ->get('/notifications?type=announcement')
        ->assertOk()
        ->assertSee('Transcript requests now online');

    expect(substr_count($filtered->getContent(), 'data-emphasis="'))->toBe(1);

    $unfiltered = $this->actingAs($alumni)
        ->get('/notifications?type=unknown')
        ->assertOk()
        ->assertSee('Transcript requests now online')
        ->assertSee('Career mobility survey');

    expect(substr_count($unfiltered->getContent(), 'data-emphasis="'))->toBe(2);
});

it('marks every notification as read from the inbox action', function () {
    $alumni = User::factory()->create();

    foreach (['First update', 'Second update'] as $title) {
        $alumni->notifications()->create([
            'id' => (string) Str::uuid(),
            'type' => 'database',
            'data' => ['type' => 'announcement', 'title' => $title, 'message' => 'Body of the update.'],
        ]);
    }

    expect($alumni->unreadNotifications()->count())->toBe(2);

    $this->actingAs($alumni)
        ->post(route('notifications.read-all'))
        ->assertRedirect(route('notifications.index'))
        ->assertSessionHas('status', 'All notifications marked as read.');

    expect($alumni->unreadNotifications()->count())->toBe(0);
});
