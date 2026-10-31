<?php

use App\Models\Employment;
use App\Models\Graduate;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('creates 100 graduates with completed alumni and employment profiles', function () {
    $this->seed(DatabaseSeeder::class);

    $this->assertDatabaseCount('graduates', 100);
    $this->assertDatabaseCount('users', 100);
    $this->assertDatabaseCount('alumni_profiles', 100);
    $this->assertDatabaseCount('employments', 100);

    $graduate = Graduate::query()
        ->with('user.profile.currentEmployment')
        ->where('student_number', 'DEMO-ALUMNI-0001')
        ->firstOrFail();
    $profile = $graduate->user?->profile;
    $employment = $profile?->currentEmployment;

    expect($graduate->first_name)->not->toBeEmpty();
    expect($graduate->last_name)->not->toBeEmpty();
    expect($graduate->user)->not->toBeNull();
    expect($graduate->user->email)->toEndWith('@example.test');
    expect($profile)->not->toBeNull();
    expect($profile->completed_at)->not->toBeNull();
    expect($profile->phone)->not->toBeEmpty();
    expect($employment)->not->toBeNull();
    expect(array_key_exists($employment->employment_status, Employment::EMPLOYMENT_STATUSES))->toBeTrue();
    expect($employment->job_title)->not->toBeEmpty();
    expect($employment->employer)->not->toBeEmpty();

    $this->seed(DatabaseSeeder::class);

    $this->assertDatabaseCount('graduates', 100);
    $this->assertDatabaseCount('users', 100);
    $this->assertDatabaseCount('alumni_profiles', 100);
    $this->assertDatabaseCount('employments', 100);
});
