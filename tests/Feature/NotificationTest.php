<?php

use App\Models\Graduate;
use App\Models\Job;
use App\Models\JobApplication;
use App\Models\Survey;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('sends a published announcement as a notification to each user', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $alumni = User::factory()->create();

    $this->actingAs($admin)
        ->post('/admin/announcements', [
            'title' => 'Spring reunion',
            'body' => 'Join us for the annual alumni dinner.',
            'status' => 'published',
        ])
        ->assertRedirectToRoute('admin.announcements.index');

    $this->assertDatabaseHas('notifications', [
        'notifiable_id' => $alumni->id,
        'notifiable_type' => User::class,
    ]);

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
        'name' => 'Jordan Lee',
        'program' => 'Computer Science',
        'graduation_year' => 2022,
    ]);
    $eligibleAlumni = User::factory()->create([
        'student_number' => '2022-1101',
        'name' => 'Jordan Lee',
    ]);

    $this->actingAs($admin)
        ->post('/admin/surveys', [
            'title' => 'Graduate outcomes survey',
            'description' => 'Tell us about your post-grad path.',
            'questions_text' => "What is your current role?",
            'target_programs' => ['Computer Science'],
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
