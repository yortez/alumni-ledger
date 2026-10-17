<?php

use App\Models\AlumniProfile;
use App\Models\Graduate;
use App\Models\Survey;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

it('renders the alumni registration form', function () {
    $response = $this->get('/register');

    $response->assertOk()
        ->assertSee('Student number')
        ->assertSee('Create account');
});

it('removes the separate login URL while keeping sign-in on the homepage', function () {
    $this->get('/')
        ->assertOk()
        ->assertSee('Welcome back')
        ->assertSee(route('login.store'), false);

    $this->get('/login')->assertNotFound();
    $this->post('/login')->assertNotFound();
});

it('authenticates credentials posted from the homepage', function () {
    $user = User::factory()->create(['email' => 'alumni@example.com']);

    $response = $this->post('/', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $response->assertRedirectToRoute('dashboard');
    $this->assertAuthenticatedAs($user);
});

it('redirects unauthenticated visitors to the homepage', function () {
    $this->get('/dashboard')->assertRedirectToRoute('home');
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
        'first_name' => 'Morgan',
        'last_name' => 'Lee',
        'program_code' => 'ENVS',
        'batch_year' => 2020,
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
        'first_name' => 'Morgan',
        'last_name' => 'Lee',
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

it('saves profile details without requiring career information', function () {
    $user = User::factory()->create(['email' => 'old@example.com']);

    $response = $this->actingAs($user)->put('/profile', [
        'first_name' => 'Morgan',
        'middle_name' => 'Avery',
        'last_name' => 'Lee',
        'suffix' => 'Jr.',
        'birthdate' => '1990-04-13',
        'sex' => 'female',
        'civil_status' => 'single',
        'phone' => '+1 555 0100',
        'email' => 'morgan@example.com',
        'current_address' => '123 Main Street, Portland, Oregon',
        'permanent_address' => '45 Oak Avenue, Salem, Oregon',
    ]);

    $response->assertRedirectToRoute('dashboard');
    $this->assertDatabaseHas('alumni_profiles', [
        'user_id' => $user->id,
        'first_name' => 'Morgan',
        'middle_name' => 'Avery',
        'last_name' => 'Lee',
        'suffix' => 'Jr.',
        'sex' => 'female',
        'civil_status' => 'single',
        'phone' => '+1 555 0100',
        'current_address' => '123 Main Street, Portland, Oregon',
        'permanent_address' => '45 Oak Avenue, Salem, Oregon',
        'completed_at' => AlumniProfile::query()->where('user_id', $user->id)->value('completed_at'),
    ]);
    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'name' => 'Morgan Avery Lee Jr.',
        'email' => 'morgan@example.com',
        'email_verified_at' => null,
    ]);
    $profile = AlumniProfile::query()->where('user_id', $user->id)->firstOrFail();
    expect($profile->birthdate->toDateString())->toBe('1990-04-13')
        ->and($profile->completed_at)->not->toBeNull();
});

it('rejects an email address already used by another account', function () {
    User::factory()->create(['email' => 'taken@example.com']);
    $user = User::factory()->create(['email' => 'alumni@example.com']);

    $this->actingAs($user)
        ->put('/profile', [
            'first_name' => 'Morgan',
            'last_name' => 'Lee',
            'phone' => '+1 555 0100',
            'email' => 'taken@example.com',
        ])
        ->assertSessionHasErrors('email');

    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'email' => 'alumni@example.com',
    ]);
    $this->assertDatabaseMissing('alumni_profiles', ['user_id' => $user->id]);
});

it('stores profile photos and Diploma/TOR documents privately', function () {
    Storage::fake('local');
    $user = User::factory()->create();

    $this->actingAs($user)
        ->put('/profile', [
            'first_name' => 'Morgan',
            'last_name' => 'Lee',
            'phone' => '+1 555 0100',
            'email' => $user->email,
            'profile_photo' => UploadedFile::fake()->image('portrait.png'),
            'diploma_document' => UploadedFile::fake()->create('diploma.pdf', 100, 'application/pdf'),
        ])
        ->assertRedirectToRoute('dashboard');

    $profile = $user->profile()->firstOrFail();
    Storage::disk('local')->assertExists($profile->profile_photo_path);
    Storage::disk('local')->assertExists($profile->diploma_document_path);

    $this->get('/profile/files/photo')->assertOk();
    $this->get('/profile/files/diploma')->assertOk();

    $previousPhotoPath = $profile->profile_photo_path;
    $this->put('/profile', [
        'first_name' => 'Morgan',
        'last_name' => 'Lee',
        'phone' => '+1 555 0100',
        'email' => $user->email,
        'profile_photo' => UploadedFile::fake()->image('replacement.png'),
    ])->assertRedirectToRoute('dashboard');

    $profile->refresh();
    Storage::disk('local')->assertMissing($previousPhotoPath);
    Storage::disk('local')->assertExists($profile->diploma_document_path);
});

it('rejects unsupported profile upload types', function () {
    Storage::fake('local');
    $user = User::factory()->create();

    $this->actingAs($user)
        ->put('/profile', [
            'first_name' => 'Morgan',
            'last_name' => 'Lee',
            'phone' => '+1 555 0100',
            'email' => $user->email,
            'profile_photo' => UploadedFile::fake()->create('portrait.svg', 50, 'image/svg+xml'),
            'diploma_document' => UploadedFile::fake()->create('diploma.exe', 50, 'application/x-msdownload'),
        ])
        ->assertSessionHasErrors(['profile_photo', 'diploma_document']);

    $this->assertDatabaseMissing('alumni_profiles', ['user_id' => $user->id]);
});

it('restricts private profile files to the owner and authorized graduate admins', function () {
    Storage::fake('local');
    $graduate = Graduate::query()->create([
        'student_number' => '2019-0142',
        'first_name' => 'Casey',
        'last_name' => 'Nguyen',
    ]);
    $user = User::factory()->create(['student_number' => '2019-0142']);
    $photoPath = UploadedFile::fake()->image('portrait.png')->store('alumni-profiles/'.$user->id, 'local');
    $documentPath = UploadedFile::fake()->create('diploma.pdf', 100, 'application/pdf')->store('alumni-profiles/'.$user->id, 'local');
    $user->profile()->create([
        'profile_photo_path' => $photoPath,
        'diploma_document_path' => $documentPath,
    ]);

    $this->actingAs($user)->get('/profile/files/photo')->assertOk();

    $otherUser = User::factory()->create();
    $this->actingAs($otherUser)->get('/profile/files/diploma')->assertNotFound();
    $this->get('/admin/graduates/'.$graduate->getKey().'/files/diploma')->assertForbidden();

    $admin = User::factory()->create();
    $admin->forceFill(['is_admin' => true])->save();

    $this->actingAs($admin)->get('/admin/graduates/'.$graduate->getKey().'/files/photo')->assertOk();
    $this->get('/admin/graduates/'.$graduate->getKey().'/files/diploma')->assertOk();
});

it('shows account and graduate information in the alumni profile form', function () {
    Graduate::query()->create([
        'student_number' => '2020-0042',
        'first_name' => 'Morgan',
        'last_name' => 'Lee',
        'program_code' => 'ENVS',
        'batch_year' => 2020,
        'latin_honor' => 'cum_laude',
    ]);
    $user = User::factory()->create([
        'name' => 'Morgan Lee',
        'student_number' => '2020-0042',
        'email' => 'morgan@example.com',
    ]);

    $this->actingAs($user)
        ->get('/profile')
        ->assertOk()
        ->assertSee('Personal Information')
        ->assertSee('Academic Information')
        ->assertDontSee('Career Information')
        ->assertSee('Employment history')
        ->assertSee('name="first_name"', false)
        ->assertSee('name="birthdate"', false)
        ->assertSee('name="sex"', false)
        ->assertSee('name="civil_status"', false)
        ->assertSee('name="current_address"', false)
        ->assertSee('name="permanent_address"', false)
        ->assertSee('name="profile_photo"', false)
        ->assertSee('name="diploma_document"', false)
        ->assertSee('morgan@example.com')
        ->assertSee('ENVS')
        ->assertSee('2020')
        ->assertSee('Cum Laude');
});

it('shows the alumni welcome banner and career action on the dashboard', function () {
    Graduate::query()->create([
        'student_number' => '2020-0042',
        'first_name' => 'Morgan',
        'last_name' => 'Lee',
        'program_code' => 'BSIT',
        'batch_year' => 2020,
    ]);
    $user = User::factory()->create([
        'name' => 'Morgan Lee',
        'student_number' => '2020-0042',
    ]);

    $this->actingAs($user)
        ->get('/dashboard')
        ->assertOk()
        ->assertSee('Welcome back, MORGAN LEE!')
        ->assertSee('BSIT')
        ->assertSee('Batch 2020')
        ->assertSee('Verified')
        ->assertSee('My Profile')
        ->assertSee('Update Career')
        ->assertSee('href="'.route('profile.edit').'#employment-history"', false);
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
        'first_name' => 'Taylor',
        'last_name' => 'Rivera',
    ]);
    $admin = User::factory()->create(['student_number' => '2018-0099']);
    $admin->forceFill(['is_admin' => true])->save();
    $profile = $admin->profile()->create([
        'city' => 'Boston',
        'country' => 'United States',
        'completed_at' => now(),
    ]);
    $profile->employments()->create([
        'job_title' => 'Senior Analyst',
        'employer' => 'Civic Lab',
        'location' => 'Boston',
        'start_date' => '2022-01-01',
        'is_current' => true,
    ]);

    $this->actingAs($admin)
        ->get('/admin/graduates')
        ->assertOk()
        ->assertSee('Alumni Records')
        ->assertSee('Employed')
        ->assertSee('Senior Analyst')
        ->assertSee('Civic Lab')
        ->assertSee('Verified');
});

it('filters alumni records and offers a matching CSV export', function () {
    Graduate::query()->create([
        'student_number' => '2023-0100',
        'first_name' => 'Taylor',
        'last_name' => 'Rivera',
        'program_code' => 'BSIT',
        'batch_year' => 2023,
    ]);
    User::factory()->create([
        'student_number' => '2023-0100',
        'email' => 'taylor@example.com',
    ]);
    Graduate::query()->create([
        'student_number' => '2022-0200',
        'first_name' => 'Jordan',
        'last_name' => 'Smith',
        'program_code' => 'BSCS',
        'batch_year' => 2022,
    ]);
    $admin = User::factory()->create();
    $admin->forceFill(['is_admin' => true])->save();

    $this->actingAs($admin)
        ->get('/admin/graduates?search=taylor%40example.com&program=BSIT&batch=2023&status=verified')
        ->assertOk()
        ->assertSee('RIVERA, TAYLOR')
        ->assertDontSee('SMITH, JORDAN');

    $this->get('/admin/graduates/export?program=BSIT&batch=2023&status=verified')
        ->assertDownload('alumni-records.csv');
});

it('allows administrators to view applicant details', function () {
    $graduate = Graduate::query()->create([
        'student_number' => '2012-7654',
        'first_name' => 'Jordan',
        'last_name' => 'Smith',
        'program_code' => 'BUS',
        'batch_year' => 2012,
    ]);
    $applicant = User::factory()->create([
        'name' => 'Jordan Smith',
        'student_number' => '2012-7654',
        'username' => 'jordansmith',
        'email' => 'jordan@example.com',
    ]);
    $profile = $applicant->profile()->create([
        'first_name' => 'Jordan',
        'middle_name' => 'Parker',
        'last_name' => 'Smith',
        'suffix' => 'Jr.',
        'birthdate' => '1995-01-02',
        'sex' => 'male',
        'civil_status' => 'single',
        'phone' => '+1 555 0102',
        'current_address' => '12 Pine Street, Seattle',
        'permanent_address' => '45 Oak Road, Olympia',
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
    $profile->employments()->create([
        'job_title' => 'Program Assistant',
        'employer' => 'Community Works',
        'employment_status' => 'employed_local',
        'sector' => 'private',
        'industry' => 'Community services',
        'job_level' => 'rank_file',
        'monthly_income' => 'prefer_not_to_say',
        'country' => 'Philippines',
        'months_to_employment' => 3,
        'course_relevance' => 'somewhat_relevant',
        'start_date' => '2017-06-01',
        'end_date' => '2020-05-31',
        'is_current' => false,
    ]);
    $survey = Survey::query()->create([
        'title' => 'Community priorities',
        'questions' => ['What should we improve?'],
        'target_programs' => [],
        'target_graduation_years' => [],
        'is_active' => true,
    ]);
    $survey->responses()->create([
        'user_id' => $applicant->id,
        'answers' => ['More alumni events.'],
        'submitted_at' => now(),
    ]);
    $admin = User::factory()->create();
    $admin->forceFill(['is_admin' => true])->save();

    $this->actingAs($admin)
        ->get('/admin/graduates/'.$graduate->getKey())
        ->assertOk()
        ->assertSee('Alumni profile')
        ->assertSee('JORDAN PARKER SMITH JR.')
        ->assertSee('Contact Information')
        ->assertSee('Academic Details')
        ->assertSee('+1 555 0102')
        ->assertSee('Jan 2, 1995')
        ->assertSee('Male')
        ->assertSee('Single')
        ->assertSee('12 Pine Street, Seattle')
        ->assertSee('45 Oak Road, Olympia')
        ->assertSee('Seattle')
        ->assertDontSee('Operations Manager')
        ->assertDontSee('Northline Group')
        ->assertSee('Career History')
        ->assertSee('Manage')
        ->assertSee('Program Assistant')
        ->assertSee('Employed (Local)')
        ->assertSee('Private')
        ->assertSee('Community services')
        ->assertSee('3 months to employment')
        ->assertSee('Community Works')
        ->assertSee('Survey Responses')
        ->assertSee('Community priorities')
        ->assertSee('More alumni events.');
});

it('lets administrators edit graduate records in the master list', function () {
    $admin = User::factory()->create();
    $admin->forceFill(['is_admin' => true])->save();
    $graduate = Graduate::query()->create([
        'student_number' => '2021-1987',
        'first_name' => 'Avery',
        'last_name' => 'Chen',
        'program_code' => 'BIO',
        'batch_year' => 2021,
    ]);

    $this->actingAs($admin)
        ->get("/admin/graduates/{$graduate->id}/edit")
        ->assertOk()
        ->assertSee('Edit graduate');

    $this->actingAs($admin)
        ->patch("/admin/graduates/{$graduate->id}", [
            'student_number' => '2021-1987',
            'first_name' => 'Avery',
            'middle_name' => 'Jr.',
            'last_name' => 'Chen',
            'program_code' => 'ENVS',
            'batch_year' => 2022,
            'latin_honor' => 'cum_laude',
        ])
        ->assertRedirectToRoute('admin.graduates.index');

    $this->assertDatabaseHas('graduates', [
        'id' => $graduate->id,
        'first_name' => 'Avery',
        'middle_name' => 'Jr.',
        'last_name' => 'Chen',
        'program_code' => 'ENVS',
        'batch_year' => 2022,
        'latin_honor' => 'cum_laude',
    ]);
});

it('accepts each supported Latin honor', function (string $latinHonor) {
    $admin = User::factory()->create();
    $admin->forceFill(['is_admin' => true])->save();

    $this->actingAs($admin)->post('/admin/graduates', [
        'student_number' => '2024-2001',
        'first_name' => 'Alex',
        'last_name' => 'Rivera',
        'latin_honor' => $latinHonor,
    ])->assertSessionHasNoErrors();

    $this->assertDatabaseHas('graduates', [
        'student_number' => '2024-2001',
        'latin_honor' => $latinHonor,
    ]);
})->with([
    'no honor' => 'none',
    'cum laude' => 'cum_laude',
    'magna cum laude' => 'magna_cum_laude',
    'summa cum laude' => 'summa_cum_laude',
]);

it('rejects unsupported Latin honor values', function () {
    $admin = User::factory()->create();
    $admin->forceFill(['is_admin' => true])->save();

    $this->actingAs($admin)->post('/admin/graduates', [
        'student_number' => '2024-2002',
        'first_name' => 'Alex',
        'last_name' => 'Rivera',
        'latin_honor' => 'honorary',
    ])->assertSessionHasErrors('latin_honor');

    $this->assertDatabaseMissing('graduates', ['student_number' => '2024-2002']);
});

it('imports valid graduate records from a CSV for an administrator', function () {
    $admin = User::factory()->create();
    $admin->forceFill(['is_admin' => true])->save();

    $response = $this->actingAs($admin)->post('/admin/graduates/import', [
        'file' => UploadedFile::fake()->createWithContent(
            'graduates.csv',
            "student_number,first_name,last_name,middle_name,program_code,batch_year,latin_honor\n2022-0101,Avery,Chen,,CS,2022,magna_cum_laude\n",
        ),
    ]);

    $response->assertSessionHas('status', '1 graduate records imported.');
    $this->assertDatabaseHas('graduates', [
        'student_number' => '2022-0101',
        'first_name' => 'Avery',
        'last_name' => 'Chen',
        'program_code' => 'CS',
        'batch_year' => 2022,
        'latin_honor' => 'magna_cum_laude',
    ]);
});
