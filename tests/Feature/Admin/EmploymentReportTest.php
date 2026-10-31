<?php

use App\Models\AdminRole;
use App\Models\Graduate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('summarizes one current outcome per graduate in the selected cohort', function () {
    $role = AdminRole::query()->create([
        'name' => 'Employment reports',
        'type' => 'employment_reports',
        'permissions' => ['employment_reports'],
    ]);
    $admin = User::factory()->create();
    $admin->forceFill(['is_admin' => true, 'role_id' => $role->id])->save();

    $graduate = Graduate::query()->create([
        'student_number' => '2020-0001',
        'first_name' => 'Mara',
        'last_name' => 'Lee',
        'program_code' => 'CS',
        'batch_year' => 2020,
    ]);
    $alumnus = User::factory()->create(['student_number' => $graduate->student_number]);
    $profile = $alumnus->profile()->create();
    $profile->employments()->create([
        'job_title' => 'Earlier current role',
        'employer' => 'Private employer',
        'start_date' => '2019-01-01',
        'is_current' => true,
        'employment_status' => 'employed_abroad',
    ]);
    $profile->employments()->create([
        'job_title' => 'Current role',
        'employer' => 'Private employer',
        'start_date' => '2022-01-01',
        'is_current' => true,
        'employment_status' => 'employed_local',
    ]);
    $profile->employments()->create([
        'job_title' => 'Past role',
        'employer' => 'Private employer',
        'start_date' => '2024-01-01',
        'end_date' => '2024-12-31',
        'is_current' => false,
        'employment_status' => 'self_employed',
    ]);

    Graduate::query()->create([
        'student_number' => '2020-0002',
        'first_name' => 'No',
        'last_name' => 'Profile',
        'program_code' => 'CS',
        'batch_year' => 2020,
    ]);
    Graduate::query()->create([
        'student_number' => '2020-0003',
        'first_name' => 'No',
        'last_name' => 'Employment',
        'program_code' => 'CS',
        'batch_year' => 2020,
    ]);
    Graduate::query()->create([
        'student_number' => '2021-0001',
        'first_name' => 'Other',
        'last_name' => 'Cohort',
        'program_code' => 'ART',
        'batch_year' => 2021,
    ]);

    $this->actingAs($admin)
        ->get('/admin/reports/employment?program=CS&batch=2020')
        ->assertOk()
        ->assertSee('Employment outcomes')
        ->assertSee('Reports')
        ->assertViewHas('graduateCount', 3)
        ->assertViewHas('outcomes', function (array $outcomes): bool {
            expect($outcomes)->toContain([
                'key' => 'employed_local',
                'label' => 'Employed (Local)',
                'count' => 1,
                'percentage' => '33.3%',
            ]);
            expect($outcomes)->toContain([
                'key' => 'not_reported',
                'label' => 'Not reported',
                'count' => 2,
                'percentage' => '66.7%',
            ]);

            return true;
        });

    $this->get('/admin/reports/employment/summary.csv?program=CS&batch=2020')
        ->assertOk()
        ->assertDownload('employment-outcomes-summary.csv')
        ->assertStreamedContent("\"Employment outcome\",Graduates,\"Percentage of graduates\"\n\"Employed (Local)\",1,33.3%\n\"Employed (Abroad)\",0,0.0%\nSelf-employed,0,0.0%\nUnemployed,0,0.0%\n\"Further studies\",0,0.0%\nOther,0,0.0%\n\"Not reported\",2,66.7%\n");
});

it('keeps aggregate and person-level downloads behind separate permissions', function () {
    $aggregateRole = AdminRole::query()->create([
        'name' => 'Aggregate reports',
        'type' => 'aggregate_reports',
        'permissions' => ['employment_reports'],
    ]);
    $aggregateAdmin = User::factory()->create();
    $aggregateAdmin->forceFill(['is_admin' => true, 'role_id' => $aggregateRole->id])->save();

    $this->actingAs($aggregateAdmin)
        ->get('/admin/reports/employment')
        ->assertOk();
    $this->get('/admin/reports/employment/summary.csv')->assertOk()->assertStreamed();
    $this->get('/admin/reports/employment/details.csv')->assertForbidden();

    $detailRole = AdminRole::query()->create([
        'name' => 'Detailed reports',
        'type' => 'detailed_reports',
        'permissions' => ['employment_report_details'],
    ]);
    $detailAdmin = User::factory()->create();
    $detailAdmin->forceFill(['is_admin' => true, 'role_id' => $detailRole->id])->save();

    $this->actingAs($detailAdmin)
        ->get('/admin/reports/employment')
        ->assertOk()
        ->assertDontSee('Outcome distribution');
    $this->get('/admin/reports/employment/summary.csv')->assertForbidden();
    $this->get('/admin/reports/employment/details.csv')->assertOk()->assertStreamed();
});

it('escapes spreadsheet formulas and omits employer details from the person-level export', function () {
    $role = AdminRole::query()->create([
        'name' => 'Detailed reports',
        'type' => 'detailed_csv_reports',
        'permissions' => ['employment_report_details'],
    ]);
    $admin = User::factory()->create();
    $admin->forceFill(['is_admin' => true, 'role_id' => $role->id])->save();
    $graduate = Graduate::query()->create([
        'student_number' => '2020-0001',
        'first_name' => '=1+1',
        'last_name' => 'Lee',
        'program_code' => 'CS',
        'batch_year' => 2020,
    ]);
    $alumnus = User::factory()->create(['student_number' => $graduate->student_number]);
    $alumnus->profile()->create()->employments()->create([
        'job_title' => 'Private title',
        'employer' => 'Private employer',
        'start_date' => '2022-01-01',
        'is_current' => true,
        'employment_status' => 'employed_local',
        'sector' => 'private',
    ]);

    $this->actingAs($admin)
        ->get('/admin/reports/employment/details.csv')
        ->assertOk()
        ->assertDownload('employment-outcomes-details.csv')
        ->assertStreamedContent("\"Student number\",\"First name\",\"Middle name\",\"Last name\",Program,Batch,\"Employment status\",Sector,Industry,\"Job level\",\"Monthly income\",Country,\"Months to employment\",\"Course relevance\"\n2020-0001,'=1+1,,Lee,CS,2020,\"Employed (Local)\",Private,,,,,,\n");
});
