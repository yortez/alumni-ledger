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
        ->assertSee('Survey manager')
        ->assertSee('Create Survey')
        ->assertSee('data-question-builder', false)
        ->assertSee('Add Question');
});

it('shows the survey builder on its own page', function () {
    $admin = User::factory()->create();
    $admin->forceFill(['is_admin' => true])->save();

    $this->actingAs($admin)
        ->get('/admin/surveys/create')
        ->assertOk()
        ->assertSee('Create survey')
        ->assertSee('Add Question')
        ->assertSee('Question type');
});

it('filters surveys by title and status', function () {
    $admin = User::factory()->create();
    $admin->forceFill(['is_admin' => true])->save();
    Survey::query()->create([
        'title' => 'Career pathways',
        'questions' => ['What is your role?'],
        'target_programs' => [],
        'target_graduation_years' => [],
        'is_active' => true,
    ]);
    Survey::query()->create([
        'title' => 'Campus feedback',
        'questions' => ['What should change?'],
        'target_programs' => [],
        'target_graduation_years' => [],
        'is_active' => false,
    ]);

    $this->actingAs($admin)
        ->get('/admin/surveys?search=career&status=open')
        ->assertOk()
        ->assertSee('Career pathways')
        ->assertDontSee('Campus feedback');
});

it('creates a survey with selected programs and graduation years', function () {
    $admin = User::factory()->create();
    $admin->forceFill(['is_admin' => true])->save();
    Graduate::query()->create([
        'student_number' => '2022-1001',
        'first_name' => 'Alex',
        'last_name' => 'Chen',
        'program_code' => 'CS',
        'batch_year' => 2022,
    ]);

    $response = $this->actingAs($admin)->post('/admin/surveys', [
        'title' => 'Career pathways',
        'description' => 'Tell us about your first role after college.',
        'questions_text' => "What was your first role?\nHow did you find it?",
        'target_programs' => ['CS'],
        'target_graduation_years' => ['2022'],
        'status' => 'active',
    ]);

    $response->assertRedirectToRoute('admin.surveys.index');
    $survey = Survey::query()->firstOrFail();
    expect($survey->normalizedQuestions())->toBe([
        ['prompt' => 'What was your first role?', 'section_label' => '', 'type' => 'long_text', 'required' => true, 'options' => []],
        ['prompt' => 'How did you find it?', 'section_label' => '', 'type' => 'long_text', 'required' => true, 'options' => []],
    ])
        ->and($survey->target_programs)->toBe(['CS'])
        ->and($survey->target_graduation_years)->toBe([2022])
        ->and($survey->is_active)->toBeTrue();
});

it('creates typed questions with section labels and choice options', function () {
    $admin = User::factory()->create();
    $admin->forceFill(['is_admin' => true])->save();

    $this->actingAs($admin)->post('/admin/surveys', [
        'title' => 'Employment details',
        'questions' => [
            ['prompt' => 'Employer name', 'section_label' => 'Employment Status', 'type' => 'short_text', 'required' => '1'],
            ['prompt' => 'Which sector?', 'section_label' => 'Employment Status', 'type' => 'radio', 'required' => '0', 'options_text' => "Private\nPublic\nOther"],
        ],
        'target_programs' => [],
        'target_graduation_years' => [],
        'status' => 'draft',
    ])->assertRedirectToRoute('admin.surveys.index');

    $survey = Survey::query()->where('title', 'Employment details')->firstOrFail();
    expect($survey->normalizedQuestions())->toBe([
        ['prompt' => 'Employer name', 'section_label' => 'Employment Status', 'type' => 'short_text', 'required' => true, 'options' => []],
        ['prompt' => 'Which sector?', 'section_label' => 'Employment Status', 'type' => 'radio', 'required' => false, 'options' => ['Private', 'Public', 'Other']],
    ]);
});

it('only offers a targeted survey to alumni matching its program and graduation year', function () {
    Graduate::query()->create([
        'student_number' => '2022-1001',
        'first_name' => 'Alex',
        'last_name' => 'Chen',
        'program_code' => 'CS',
        'batch_year' => 2022,
    ]);
    Graduate::query()->create([
        'student_number' => '2021-1002',
        'first_name' => 'Blair',
        'last_name' => 'Kim',
        'program_code' => 'CS',
        'batch_year' => 2021,
    ]);
    Graduate::query()->create([
        'student_number' => '2022-1003',
        'first_name' => 'Casey',
        'last_name' => 'Brown',
        'program_code' => 'HIST',
        'batch_year' => 2022,
    ]);
    $survey = Survey::query()->create([
        'title' => 'Career pathways',
        'questions' => ['What was your first role?'],
        'target_programs' => ['CS'],
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
        'first_name' => 'Drew',
        'last_name' => 'Patel',
        'program_code' => 'BIO',
        'batch_year' => 2023,
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

it('lists the surveys open to an alumnus', function () {
    Graduate::query()->create([
        'student_number' => '2022-4400',
        'first_name' => 'Nina',
        'last_name' => 'Reyes',
        'program_code' => 'CS',
        'batch_year' => 2022,
    ]);
    $alumnus = User::factory()->create(['student_number' => '2022-4400']);
    Survey::query()->create([
        'title' => 'Career pathways',
        'description' => 'Tell us about your first role.',
        'questions' => ['What was your first role?'],
        'target_programs' => [],
        'target_graduation_years' => [],
        'is_active' => true,
        'closes_at' => now()->addWeeks(2),
    ]);

    $this->actingAs($alumnus)
        ->get('/surveys')
        ->assertOk()
        ->assertSee('Career pathways')
        ->assertSee('1 question');
});

it('renders and stores typed survey answers including multiple selections', function () {
    Graduate::query()->create([
        'student_number' => '2024-5510',
        'first_name' => 'Sam',
        'last_name' => 'Garcia',
        'program_code' => 'CS',
        'batch_year' => 2024,
    ]);
    $alumnus = User::factory()->create(['student_number' => '2024-5510']);
    $survey = Survey::query()->create([
        'title' => 'Employment profile',
        'questions' => [
            ['prompt' => 'Employer name', 'section_label' => 'Employment Status', 'type' => 'short_text', 'required' => true, 'options' => []],
            ['prompt' => 'Sector', 'section_label' => 'Employment Status', 'type' => 'radio', 'required' => true, 'options' => ['Private', 'Public']],
            ['prompt' => 'Benefits', 'section_label' => 'Employment Status', 'type' => 'checkbox', 'required' => false, 'options' => ['Insurance', 'Leave', 'Training']],
            ['prompt' => 'Course relevance', 'section_label' => 'Education', 'type' => 'dropdown', 'required' => false, 'options' => ['Highly relevant', 'Somewhat relevant', 'Not relevant']],
        ],
        'target_programs' => [],
        'target_graduation_years' => [],
        'is_active' => true,
    ]);

    $this->actingAs($alumnus)
        ->get("/surveys/{$survey->id}")
        ->assertOk()
        ->assertSee('Employer name')
        ->assertSee('Employment Status')
        ->assertSee('type="radio"', false)
        ->assertSee('type="checkbox"', false)
        ->assertSee('Course relevance');

    $this->post("/surveys/{$survey->id}/responses", [
        'answers' => [
            'Northside Health',
            'Private',
            ['Insurance', 'Training'],
            'Somewhat relevant',
        ],
    ])->assertRedirectToRoute('surveys.index');

    expect(SurveyResponse::query()->firstOrFail()->answers)->toBe([
        'Northside Health',
        'Private',
        ['Insurance', 'Training'],
        'Somewhat relevant',
    ]);

    $admin = User::factory()->create(['is_admin' => true]);
    $this->actingAs($admin)
        ->get("/admin/surveys/{$survey->id}")
        ->assertOk()
        ->assertSee('Employer name')
        ->assertSee('Northside Health')
        ->assertSee('Insurance, Training');
});

it('rejects typed choice answers that are not survey options', function () {
    Graduate::query()->create([
        'student_number' => '2024-5511',
        'first_name' => 'Taylor',
        'last_name' => 'Ng',
        'program_code' => 'CS',
        'batch_year' => 2024,
    ]);
    $alumnus = User::factory()->create(['student_number' => '2024-5511']);
    $survey = Survey::query()->create([
        'title' => 'Employment sector',
        'questions' => [[
            'prompt' => 'Choose your sector',
            'section_label' => 'Employment',
            'type' => 'radio',
            'required' => true,
            'options' => ['Private', 'Public'],
        ]],
        'target_programs' => [],
        'target_graduation_years' => [],
        'is_active' => true,
    ]);

    $this->actingAs($alumnus)
        ->post("/surveys/{$survey->id}/responses", ['answers' => ['Not an option']])
        ->assertSessionHasErrors('answers.0');

    $this->assertDatabaseCount('survey_responses', 0);
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
        ->get('/admin/surveys')
        ->assertOk()
        ->assertSee('data-survey-edit-open', false)
        ->assertSee('id="survey-edit-dialog"', false);

    $this->actingAs($admin)
        ->get("/admin/surveys/{$survey->id}/edit")
        ->assertOk()
        ->assertSee('Edit survey')
        ->assertSee('Section label')
        ->assertSee('Required');

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
    expect($survey->fresh()->normalizedQuestions())->toBe([
        ['prompt' => 'What should we improve?', 'section_label' => '', 'type' => 'long_text', 'required' => true, 'options' => []],
        ['prompt' => 'Anything else?', 'section_label' => '', 'type' => 'long_text', 'required' => true, 'options' => []],
    ]);
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
