<?php

use App\Models\Graduate;
use App\Models\Job;
use App\Models\JobApplication;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('allows an administrator to create and publish a job', function () {
    $admin = User::factory()->create(['is_admin' => true]);

    $response = $this->actingAs($admin)->post('/admin/jobs', [
        'title' => 'Community Partnerships Lead',
        'company' => 'Civic Lab',
        'location' => 'Boston, MA',
        'employment_type' => 'Full-time',
        'description' => 'Build partnerships with local organizations.',
        'requirements' => 'Five years of relevant experience.',
        'application_deadline' => now()->addMonth()->toDateString(),
        'status' => 'published',
    ]);

    $response->assertRedirectToRoute('admin.jobs.index');
    $this->assertDatabaseHas('job_postings', [
        'title' => 'Community Partnerships Lead',
        'created_by' => $admin->id,
        'status' => 'published',
    ]);
});

it('only shows published and current jobs to alumni', function () {
    $user = User::factory()->create();
    Job::factory()->for($user, 'creator')->create(['title' => 'Open role', 'status' => 'published']);
    Job::factory()->for($user, 'creator')->create(['title' => 'Draft role', 'status' => 'draft']);
    Job::factory()->for($user, 'creator')->create(['title' => 'Expired role', 'status' => 'published', 'application_deadline' => now()->subDay()]);

    $response = $this->actingAs($user)->get('/jobs');

    $response->assertOk()->assertSee('Open role')->assertDontSee('Draft role')->assertDontSee('Expired role');
});

it('forbids non administrators from managing jobs', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get('/admin/jobs')->assertForbidden();
});

it('requires a completed profile before an alumni can apply', function () {
    $user = User::factory()->create();
    $job = Job::factory()->create();

    $response = $this->actingAs($user)->post(route('jobs.applications.store', $job), [
        'cover_letter' => 'I would love to join the team.',
    ]);

    $response->assertRedirectToRoute('profile.edit')
        ->assertSessionHasErrors('application');
    $this->assertDatabaseCount('job_applications', 0);
});

it('allows a profiled alumni to apply once per job', function () {
    $user = User::factory()->create();
    $user->profile()->create([
        'phone' => '+1 555 0100',
        'job_title' => 'Research Associate',
        'employer' => 'Field Lab',
        'industry' => 'Research',
        'employment_status' => 'employed',
        'city' => 'Boston',
        'country' => 'United States',
        'completed_at' => now(),
    ]);
    $job = Job::factory()->create();

    $response = $this->actingAs($user)->post(route('jobs.applications.store', $job), [
        'cover_letter' => 'I would love to join the team.',
    ]);

    $response->assertRedirectToRoute('jobs.show', $job);
    $this->assertDatabaseHas('job_applications', [
        'job_id' => $job->id,
        'user_id' => $user->id,
        'status' => 'submitted',
    ]);

    $duplicateResponse = $this->actingAs($user)->post(route('jobs.applications.store', $job), [
        'cover_letter' => 'A second submission.',
    ]);

    $duplicateResponse->assertSessionHasErrors('application');
    expect(JobApplication::query()->whereBelongsTo($job)->whereBelongsTo($user)->count())->toBe(1);
});

it('lets administrators review a candidate profile and update the application status', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $applicant = User::factory()->create([
        'name' => 'Jamie Patel',
        'student_number' => '2019-2210',
        'username' => 'jamiepatel',
        'email' => 'jamie@example.com',
    ]);
    $applicant->profile()->create([
        'phone' => '+1 555 0107',
        'job_title' => 'Research Associate',
        'employer' => 'Civic Lab',
        'industry' => 'Public policy',
        'employment_status' => 'employed',
        'city' => 'Boston',
        'country' => 'United States',
        'completed_at' => now(),
    ]);
    Graduate::query()->create([
        'student_number' => '2019-2210',
        'name' => 'Jamie Patel',
    ]);
    $job = Job::factory()->for($admin, 'creator')->create(['title' => 'Program Coordinator', 'status' => 'published']);
    $application = JobApplication::factory()->for($job, 'job')->for($applicant, 'user')->create([
        'status' => 'submitted',
        'cover_letter' => 'I am excited to contribute to your program team.',
    ]);

    $indexResponse = $this->actingAs($admin)->get(route('admin.jobs.applications.index', $job));

    $indexResponse->assertOk()
        ->assertSee('View profile');

    $detailResponse = $this->actingAs($admin)->get(route('admin.jobs.applications.show', [$job, $application]));

    $detailResponse->assertOk()
        ->assertSee('Applicant profile')
        ->assertSee('Research Associate')
        ->assertSee('Civic Lab');

    $updateResponse = $this->actingAs($admin)->patch(route('admin.jobs.applications.update', [$job, $application]), [
        'status' => 'interview_scheduled',
    ]);

    $updateResponse->assertRedirect(route('admin.jobs.applications.index', $job))
        ->assertSessionHas('status', 'Application status updated to Interview scheduled.');
    $this->assertDatabaseHas('job_applications', [
        'id' => $application->id,
        'status' => 'interview_scheduled',
    ]);
});
