<?php

use App\Models\Graduate;
use App\Models\Job;
use App\Models\JobApplication;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('allows an administrator to create and publish a job', function () {
    $admin = User::factory()->create(['is_admin' => true]);

    $this->actingAs($admin)
        ->get('/admin/jobs/create')
        ->assertOk()
        ->assertSee('Create job posting')
        ->assertSee('Application deadline');

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

it('filters admin job postings by search and status', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    Job::factory()->create(['title' => 'Research Coordinator', 'company' => 'Civic Lab', 'status' => 'published']);
    Job::factory()->create(['title' => 'Office Assistant', 'company' => 'North Group', 'status' => 'draft']);

    $this->actingAs($admin)
        ->get('/admin/jobs?search=Civic&status=published')
        ->assertOk()
        ->assertSee('Research Coordinator')
        ->assertSee('data-job-edit-open', false)
        ->assertSee('id="job-edit-dialog"', false)
        ->assertDontSee('title="Edit job"', false)
        ->assertDontSee('Office Assistant');
});

it('lets administrators edit a published job posting', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $job = Job::factory()->for($admin, 'creator')->create([
        'title' => 'Program Coordinator',
        'company' => 'Former Org',
        'location' => 'Remote',
        'employment_type' => 'Contract',
        'description' => 'Old description.',
        'requirements' => 'Old requirements.',
        'status' => 'draft',
    ]);

    $this->actingAs($admin)
        ->get("/admin/jobs/{$job->id}/edit")
        ->assertOk()
        ->assertSee('Edit job posting');

    $this->actingAs($admin)
        ->patch("/admin/jobs/{$job->id}", [
            'title' => 'Senior Program Coordinator',
            'company' => 'Updated Org',
            'location' => 'Boston, MA',
            'employment_type' => 'Full-time',
            'description' => 'Updated description.',
            'requirements' => 'Updated requirements.',
            'application_deadline' => now()->addWeek()->toDateString(),
            'status' => 'published',
        ])
        ->assertRedirectToRoute('admin.jobs.index');

    $this->assertDatabaseHas('job_postings', [
        'id' => $job->id,
        'title' => 'Senior Program Coordinator',
        'company' => 'Updated Org',
        'location' => 'Boston, MA',
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

it('shows a limited preview of published and current jobs on the alumni dashboard', function () {
    $user = User::factory()->create();
    $oldestJob = Job::factory()->for($user, 'creator')->create([
        'title' => 'Oldest dashboard role',
        'status' => 'published',
    ]);
    $oldestJob->forceFill(['created_at' => now()->subDays(5)])->save();
    Job::factory()->for($user, 'creator')->create(['title' => 'Current role one', 'status' => 'published']);
    Job::factory()->for($user, 'creator')->create(['title' => 'Current role two', 'status' => 'published']);
    Job::factory()->for($user, 'creator')->create(['title' => 'Current role three', 'status' => 'published']);
    Job::factory()->for($user, 'creator')->create(['title' => 'Draft dashboard role', 'status' => 'draft']);
    Job::factory()->for($user, 'creator')->create([
        'title' => 'Expired dashboard role',
        'status' => 'published',
        'application_deadline' => now()->subDay(),
    ]);

    $this->actingAs($user)
        ->get('/dashboard')
        ->assertOk()
        ->assertSee('Current role one')
        ->assertSee('Current role two')
        ->assertSee('Current role three')
        ->assertDontSee('Oldest dashboard role')
        ->assertDontSee('Draft dashboard role')
        ->assertDontSee('Expired dashboard role');
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
    $profile = $applicant->profile()->create([
        'phone' => '+1 555 0107',
        'current_address' => '42 Beacon Street',
        'bio' => 'Researcher focused on equitable public services.',
        'linkedin_url' => 'https://www.linkedin.com/in/jamiepatel',
        'job_title' => 'Research Associate',
        'employer' => 'Civic Lab',
        'industry' => 'Public policy',
        'employment_status' => 'employed',
        'city' => 'Boston',
        'country' => 'United States',
        'completed_at' => now(),
    ]);
    $profile->employments()->create([
        'job_title' => 'Policy Intern',
        'employer' => 'City Office',
        'start_date' => '2017-06-01',
        'end_date' => '2019-05-31',
        'is_current' => false,
    ]);
    Graduate::query()->create([
        'student_number' => '2019-2210',
        'first_name' => 'Jamie',
        'last_name' => 'Patel',
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
        ->assertSee('Civic Lab')
        ->assertSee('+1 555 0107')
        ->assertSee('42 Beacon Street')
        ->assertSee('Researcher focused on equitable public services.')
        ->assertSee('https://www.linkedin.com/in/jamiepatel')
        ->assertSee('Employment history')
        ->assertSee('Policy Intern')
        ->assertSee('City Office');

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
