<?php

use App\Models\AlumniProfile;
use App\Models\Graduate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

it('renders the alumni registration form', function () {
    $response = $this->get('/register');

    $response->assertOk()
        ->assertSee('Student number')
        ->assertSee('Create account');
});

it('rejects registration when the student number is not in the master list', function () {
    $response = $this->post('/register', [
        'student_number' => 'NO-SUCH-GRADUATE',
        'username' => 'newalum',
        'email' => 'newalum@example.com',
        'password' => 'a-secure-password',
        'password_confirmation' => 'a-secure-password',
    ]);

    $response->assertSessionHasErrors([
        'student_number' => 'We could not find that student number in the graduate master list.',
    ]);
    $this->assertDatabaseCount('users', 0);
});

it('creates an alumni account from a matching graduate record', function () {
    Graduate::query()->create([
        'student_number' => '2020-0042',
        'name' => 'Morgan Lee',
        'program' => 'Environmental Science',
        'graduation_year' => 2020,
    ]);

    $response = $this->post('/register', [
        'student_number' => '2020-0042',
        'username' => 'morganlee',
        'email' => 'morgan@example.com',
        'password' => 'a-secure-password',
        'password_confirmation' => 'a-secure-password',
    ]);

    $response->assertRedirectToRoute('dashboard');
    $this->assertAuthenticated();
    $this->assertDatabaseHas('users', [
        'name' => 'Morgan Lee',
        'username' => 'morganlee',
        'student_number' => '2020-0042',
        'email' => 'morgan@example.com',
    ]);
    expect(Hash::check('a-secure-password', User::query()->where('email', 'morgan@example.com')->value('password')))->toBeTrue();
});

it('prevents a graduate record from being claimed twice', function () {
    Graduate::query()->create([
        'student_number' => '2020-0042',
        'name' => 'Morgan Lee',
    ]);
    User::factory()->create(['student_number' => '2020-0042']);

    $response = $this->post('/register', [
        'student_number' => '2020-0042',
        'username' => 'anotheruser',
        'email' => 'another@example.com',
        'password' => 'a-secure-password',
        'password_confirmation' => 'a-secure-password',
    ]);

    $response->assertSessionHasErrors([
        'student_number' => 'An account has already been created for that student number.',
    ]);
    $this->assertDatabaseCount('users', 1);
});

it('saves a complete alumni profile for the signed-in account', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->put('/profile', [
        'phone' => '+1 555 0100',
        'job_title' => 'Research Associate',
        'employer' => 'Field Lab',
        'industry' => 'Research',
        'employment_status' => 'employed',
        'city' => 'Portland',
        'country' => 'United States',
        'linkedin_url' => 'https://www.linkedin.com/in/morgan-lee',
        'bio' => 'Working on urban ecology.',
    ]);

    $response->assertRedirectToRoute('dashboard');
    $this->assertDatabaseHas('alumni_profiles', [
        'user_id' => $user->id,
        'job_title' => 'Research Associate',
        'employer' => 'Field Lab',
        'completed_at' => AlumniProfile::query()->where('user_id', $user->id)->value('completed_at'),
    ]);
    expect(AlumniProfile::query()->where('user_id', $user->id)->firstOrFail()->completed_at)->not->toBeNull();
});

it('forbids non-admin users from viewing the graduate master list', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/admin/graduates')
        ->assertForbidden();
});

it('shows profile completion and employment details to administrators', function () {
    Graduate::query()->create([
        'student_number' => '2018-0099',
        'name' => 'Taylor Rivera',
    ]);
    $admin = User::factory()->create(['student_number' => '2018-0099']);
    $admin->forceFill(['is_admin' => true])->save();
    $admin->profile()->create([
        'job_title' => 'Senior Analyst',
        'employer' => 'Civic Lab',
        'industry' => 'Public policy',
        'city' => 'Boston',
        'country' => 'United States',
        'completed_at' => now(),
    ]);

    $this->actingAs($admin)
        ->get('/admin/graduates')
        ->assertOk()
        ->assertSee('Profiles complete')
        ->assertSee('Senior Analyst')
        ->assertSee('Civic Lab');
});

it('allows administrators to view applicant details', function () {
    $graduate = Graduate::query()->create([
        'student_number' => '2012-7654',
        'name' => 'Jordan Smith',
        'program' => 'Business Administration',
        'graduation_year' => 2012,
    ]);
    $applicant = User::factory()->create([
        'name' => 'Jordan Smith',
        'student_number' => '2012-7654',
        'username' => 'jordansmith',
        'email' => 'jordan@example.com',
    ]);
    $applicant->profile()->create([
        'phone' => '+1 555 0102',
        'job_title' => 'Operations Manager',
        'employer' => 'Northline Group',
        'industry' => 'Logistics',
        'employment_status' => 'employed',
        'city' => 'Seattle',
        'country' => 'United States',
        'linkedin_url' => 'https://www.linkedin.com/in/jordan-smith',
        'bio' => 'Focused on operational growth.',
        'completed_at' => now(),
    ]);
    $admin = User::factory()->create();
    $admin->forceFill(['is_admin' => true])->save();

    $this->actingAs($admin)
        ->get('/admin/graduates/'.$graduate->getKey())
        ->assertOk()
        ->assertSee('Applicant details')
        ->assertSee('Jordan Smith')
        ->assertSee('Operations Manager')
        ->assertSee('Northline Group');
});

it('lets administrators edit graduate records in the master list', function () {
    $admin = User::factory()->create();
    $admin->forceFill(['is_admin' => true])->save();
    $graduate = Graduate::query()->create([
        'student_number' => '2021-1987',
        'name' => 'Avery Chen',
        'program' => 'Biology',
        'graduation_year' => 2021,
    ]);

    $this->actingAs($admin)
        ->get("/admin/graduates/{$graduate->id}/edit")
        ->assertOk()
        ->assertSee('Edit graduate');

    $this->actingAs($admin)
        ->patch("/admin/graduates/{$graduate->id}", [
            'student_number' => '2021-1987',
            'name' => 'Avery Chen Jr.',
            'program' => 'Environmental Science',
            'graduation_year' => 2022,
        ])
        ->assertRedirectToRoute('admin.graduates.index');

    $this->assertDatabaseHas('graduates', [
        'id' => $graduate->id,
        'name' => 'Avery Chen Jr.',
        'program' => 'Environmental Science',
        'graduation_year' => 2022,
    ]);
});

it('imports valid graduate records from a CSV for an administrator', function () {
    $admin = User::factory()->create();
    $admin->forceFill(['is_admin' => true])->save();

    $response = $this->actingAs($admin)->post('/admin/graduates/import', [
        'file' => UploadedFile::fake()->createWithContent(
            'graduates.csv',
            "student_number,name,program,graduation_year\n2022-0101,Avery Chen,Computer Science,2022\n",
        ),
    ]);

    $response->assertSessionHas('status', '1 graduate records imported.');
    $this->assertDatabaseHas('graduates', [
        'student_number' => '2022-0101',
        'name' => 'Avery Chen',
        'program' => 'Computer Science',
        'graduation_year' => 2022,
    ]);
});
