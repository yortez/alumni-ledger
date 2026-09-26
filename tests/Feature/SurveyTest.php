<?php

use App\Models\Graduate;
use App\Models\Survey;
use App\Models\SurveyResponse;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('shows the survey manager to administrators', function () {
    $admin = User::factory()->create();
    $admin->forceFill(['is_admin' => true])->save();

    $this->actingAs($admin)
        ->get('/admin/surveys')
        ->assertOk()
        ->assertSee('Survey manager');
});

it('creates a survey with selected programs and graduation years', function () {
    $admin = User::factory()->create();
    $admin->forceFill(['is_admin' => true])->save();
    Graduate::query()->create([
        'student_number' => '2022-1001',
        'name' => 'Alex Chen',
        'program' => 'Computer Science',
        'graduation_year' => 2022,
    ]);

    $response = $this->actingAs($admin)->post('/admin/surveys', [
        'title' => 'Career pathways',
        'description' => 'Tell us about your first role after college.',
        'questions_text' => "What was your first role?\nHow did you find it?",
        'target_programs' => ['Computer Science'],
        'target_graduation_years' => ['2022'],
        'status' => 'active',
    ]);

    $response->assertRedirectToRoute('admin.surveys.index');
    $survey = Survey::query()->firstOrFail();
    expect($survey->questions)->toBe(['What was your first role?', 'How did you find it?'])
        ->and($survey->target_programs)->toBe(['Computer Science'])
        ->and($survey->target_graduation_years)->toBe([2022])
        ->and($survey->is_active)->toBeTrue();
});

it('only offers a targeted survey to alumni matching its program and graduation year', function () {
    Graduate::query()->create([
        'student_number' => '2022-1001',
        'name' => 'Alex Chen',
        'program' => 'Computer Science',
        'graduation_year' => 2022,
    ]);
    Graduate::query()->create([
        'student_number' => '2021-1002',
        'name' => 'Blair Kim',
        'program' => 'Computer Science',
        'graduation_year' => 2021,
    ]);
    Graduate::query()->create([
        'student_number' => '2022-1003',
        'name' => 'Casey Brown',
        'program' => 'History',
        'graduation_year' => 2022,
    ]);
    $survey = Survey::query()->create([
        'title' => 'Career pathways',
        'questions' => ['What was your first role?'],
        'target_programs' => ['Computer Science'],
        'target_graduation_years' => [2022],
        'is_active' => true,
    ]);
    $matchingAlumnus = User::factory()->create(['student_number' => '2022-1001']);
    $wrongYearAlumnus = User::factory()->create(['student_number' => '2021-1002']);
    $wrongProgramAlumnus = User::factory()->create(['student_number' => '2022-1003']);

    $this->actingAs($matchingAlumnus)->get("/surveys/{$survey->id}")->assertOk()->assertSee('Career pathways');
    $this->actingAs($wrongYearAlumnus)->get("/surveys/{$survey->id}")->assertNotFound();
    $this->actingAs($wrongProgramAlumnus)->get("/surveys/{$survey->id}")->assertNotFound();
    $this->actingAs($wrongProgramAlumnus)
        ->post("/surveys/{$survey->id}/responses", ['answers' => ['Not eligible']])
        ->assertNotFound();
    $this->assertDatabaseCount('survey_responses', 0);
});

it('stores one response from an eligible alumnus and prevents a second response', function () {
    Graduate::query()->create([
        'student_number' => '2023-0091',
        'name' => 'Drew Patel',
        'program' => 'Biology',
        'graduation_year' => 2023,
    ]);
    $alumnus = User::factory()->create(['student_number' => '2023-0091']);
    $survey = Survey::query()->create([
        'title' => 'Alumni outcomes',
        'questions' => ['Where do you work?', 'What do you value most about your degree?'],
        'target_programs' => [],
        'target_graduation_years' => [],
        'is_active' => true,
    ]);

    $response = $this->actingAs($alumnus)->post("/surveys/{$survey->id}/responses", [
        'answers' => [
            'Northside Health',
            'The research experience.',
        ],
    ]);

    $response->assertRedirectToRoute('surveys.index')
        ->assertSessionHas('status', 'Your survey response has been submitted.');
    $surveyResponse = SurveyResponse::query()->firstOrFail();
    expect($surveyResponse->answers)->toBe(['Northside Health', 'The research experience.']);

    $this->actingAs($alumnus)
        ->post("/surveys/{$survey->id}/responses", ['answers' => ['Again', 'Again']])
        ->assertNotFound();
    $this->assertDatabaseCount('survey_responses', 1);
});

it('forbids alumni from creating surveys', function () {
    $alumnus = User::factory()->create();

    $this->actingAs($alumnus)
        ->post('/admin/surveys', [
            'title' => 'Private survey',
            'questions_text' => 'Question one',
            'status' => 'active',
        ])
        ->assertForbidden();
    $this->assertDatabaseCount('surveys', 0);
});

it('lets administrators edit a survey', function () {
    $admin = User::factory()->create();
    $admin->forceFill(['is_admin' => true])->save();
    $survey = Survey::query()->create([
        'title' => 'Community priorities',
        'description' => 'Old description.',
        'questions' => ['What should we improve?'],
        'target_programs' => [],
        'target_graduation_years' => [],
        'is_active' => true,
    ]);

    $this->actingAs($admin)
        ->get("/admin/surveys/{$survey->id}/edit")
        ->assertOk()
        ->assertSee('Edit survey');

    $this->actingAs($admin)
        ->patch("/admin/surveys/{$survey->id}", [
            'title' => 'Updated community priorities',
            'description' => 'New description.',
            'questions_text' => "What should we improve?\nAnything else?",
            'status' => 'active',
        ])
        ->assertRedirectToRoute('admin.surveys.index');

    $this->assertDatabaseHas('surveys', [
        'id' => $survey->id,
        'title' => 'Updated community priorities',
        'description' => 'New description.',
    ]);
    expect($survey->fresh()->questions)->toBe(['What should we improve?', 'Anything else?']);
});

it('lets administrators review responses with respondent identity', function () {
    $admin = User::factory()->create();
    $admin->forceFill(['is_admin' => true])->save();
    $alumnus = User::factory()->create(['student_number' => '2019-0044']);
    $survey = Survey::query()->create([
        'title' => 'Community priorities',
        'questions' => ['What should we improve?'],
        'target_programs' => [],
        'target_graduation_years' => [],
        'is_active' => true,
    ]);
    $survey->responses()->create([
        'user_id' => $alumnus->id,
        'answers' => ['More alumni events.'],
        'submitted_at' => now(),
    ]);

    $this->actingAs($admin)
        ->get("/admin/surveys/{$survey->id}")
        ->assertOk()
        ->assertSee('Community priorities')
        ->assertSee('2019-0044')
        ->assertSee('More alumni events.');
});
