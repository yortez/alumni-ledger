<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('stores multiple current and past employment records for an alumnus', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->post(route('profile.employments.store'), [
        'employment_status' => 'employed_local',
        'sector' => 'private',
        'job_title' => 'Research Lead',
        'employer' => 'Field Lab',
        'location' => 'Portland',
        'industry' => 'Research',
        'job_level' => 'supervisory',
        'monthly_income' => '50000_99999',
        'country' => 'Philippines',
        'months_to_employment' => 2,
        'course_relevance' => 'highly_relevant',
        'start_date' => '2024-03-01',
        'is_current' => '1',
        'description' => 'Leading field research.',
    ])->assertRedirectToRoute('profile.edit');

    $this->actingAs($user)->post(route('profile.employments.store'), [
        'employment_status' => 'employed_local',
        'sector' => 'public',
        'job_title' => 'Analyst',
        'employer' => 'Civic Lab',
        'start_date' => '2020-01-01',
        'end_date' => '2023-12-31',
        'is_current' => '0',
    ])->assertRedirectToRoute('profile.edit');

    $this->assertDatabaseCount('employments', 2);
    $this->assertDatabaseHas('employments', [
        'job_title' => 'Research Lead',
        'employment_status' => 'employed_local',
        'sector' => 'private',
        'industry' => 'Research',
        'job_level' => 'supervisory',
        'monthly_income' => '50000_99999',
        'country' => 'Philippines',
        'months_to_employment' => 2,
        'course_relevance' => 'highly_relevant',
        'is_current' => true,
        'end_date' => null,
    ]);
    $this->assertDatabaseHas('employments', [
        'job_title' => 'Analyst',
        'is_current' => false,
    ]);
    expect($user->profile()->firstOrFail()->employments()
        ->where('job_title', 'Analyst')
        ->firstOrFail()
        ->end_date->toDateString())->toBe('2023-12-31');

    $this->actingAs($user)->get(route('profile.edit'))
        ->assertSee('Employment history')
        ->assertSee('New Employment Entry')
        ->assertSee('Employment status')
        ->assertSee('Company / employer name')
        ->assertSee('Course relevance')
        ->assertSee('Employed (Local)')
        ->assertSee('Prefer not to say')
        ->assertSee('Research Lead')
        ->assertSee('Analyst');
});

it('updates and removes an alumnus employment record', function () {
    $user = User::factory()->create();
    $employment = $user->profile()->create()->employments()->create([
        'job_title' => 'Analyst',
        'employer' => 'Civic Lab',
        'start_date' => '2020-01-01',
        'end_date' => '2023-12-31',
        'is_current' => false,
    ]);

    $this->actingAs($user)->patch(route('profile.employments.update', $employment), [
        'employment_status' => 'employed_local',
        'sector' => 'private',
        'job_title' => 'Senior Analyst',
        'employer' => 'Civic Lab',
        'start_date' => '2020-01-01',
        'is_current' => '1',
    ])->assertRedirectToRoute('profile.edit');

    $this->assertDatabaseHas('employments', [
        'id' => $employment->id,
        'job_title' => 'Senior Analyst',
        'is_current' => true,
        'end_date' => null,
    ]);

    $this->actingAs($user)->delete(route('profile.employments.destroy', $employment))
        ->assertRedirectToRoute('profile.edit');

    $this->assertDatabaseMissing('employments', ['id' => $employment->id]);
});

it('returns 404 when an alumnus attempts to change another alumni employment record', function () {
    $owner = User::factory()->create();
    $employment = $owner->profile()->create()->employments()->create([
        'job_title' => 'Analyst',
        'employer' => 'Civic Lab',
        'start_date' => '2020-01-01',
        'is_current' => false,
    ]);
    $otherUser = User::factory()->create();

    $this->actingAs($otherUser)
        ->patch(route('profile.employments.update', $employment), [
            'employment_status' => 'employed_local',
            'job_title' => 'Changed title',
            'employer' => 'Civic Lab',
            'start_date' => '2020-01-01',
            'is_current' => '0',
        ])
        ->assertNotFound();

    $this->actingAs($otherUser)
        ->delete(route('profile.employments.destroy', $employment))
        ->assertNotFound();

    $this->assertDatabaseHas('employments', [
        'id' => $employment->id,
        'job_title' => 'Analyst',
    ]);
});

it('rejects an employment end date before its start date', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->post(route('profile.employments.store'), [
        'employment_status' => 'employed_local',
        'job_title' => 'Analyst',
        'employer' => 'Civic Lab',
        'start_date' => '2024-01-01',
        'end_date' => '2023-12-31',
        'is_current' => '0',
    ])->assertSessionHasErrors('end_date');

    $this->assertDatabaseCount('employments', 0);
});

it('rejects an unsupported employment status', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->post(route('profile.employments.store'), [
        'employment_status' => 'not_a_status',
        'job_title' => 'Analyst',
        'employer' => 'Civic Lab',
        'start_date' => '2024-01-01',
        'is_current' => '0',
    ])->assertSessionHasErrors('employment_status');

    $this->assertDatabaseCount('employments', 0);
});
