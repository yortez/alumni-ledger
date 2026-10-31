<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Employment;
use App\Models\Graduate;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EmploymentReportController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $this->filters($request);
        $canExportAggregate = $request->user()->hasAdminPermission('employment_reports');
        $outcomes = $canExportAggregate ? $this->outcomes($filters) : [];

        return view('admin.employment-reports', [
            'programs' => Graduate::query()->whereNotNull('program_code')->distinct()->orderBy('program_code')->pluck('program_code'),
            'batches' => Graduate::query()->whereNotNull('batch_year')->distinct()->orderByDesc('batch_year')->pluck('batch_year'),
            'filters' => $filters,
            'outcomes' => $outcomes,
            'graduateCount' => array_sum(array_column($outcomes, 'count')),
            'canExportAggregate' => $canExportAggregate,
            'canExportDetails' => $request->user()->hasAdminPermission('employment_report_details'),
        ]);
    }

    public function exportSummary(Request $request): StreamedResponse
    {
        $filters = $this->filters($request);
        $outcomes = $this->outcomes($filters);

        return response()->streamDownload(function () use ($outcomes): void {
            $stream = fopen('php://output', 'w');

            if (! is_resource($stream)) {
                return;
            }

            try {
                fputcsv($stream, ['Employment outcome', 'Graduates', 'Percentage of graduates'], ',', '"', '');

                foreach ($outcomes as $outcome) {
                    fputcsv($stream, [
                        $this->safeCsvValue($outcome['label']),
                        $outcome['count'],
                        $outcome['percentage'],
                    ], ',', '"', '');
                }
            } finally {
                fclose($stream);
            }
        }, 'employment-outcomes-summary.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function exportDetails(Request $request): StreamedResponse
    {
        $filters = $this->filters($request);

        return response()->streamDownload(function () use ($filters): void {
            $stream = fopen('php://output', 'w');

            if (! is_resource($stream)) {
                return;
            }

            try {
                fputcsv($stream, [
                    'Student number',
                    'First name',
                    'Middle name',
                    'Last name',
                    'Program',
                    'Batch',
                    'Employment status',
                    'Sector',
                    'Industry',
                    'Job level',
                    'Monthly income',
                    'Country',
                    'Months to employment',
                    'Course relevance',
                ], ',', '"', '');

                $this->graduateQuery($filters)->chunkById(500, function ($graduates) use ($stream): void {
                    foreach ($graduates as $graduate) {
                        $employment = $graduate->user?->profile?->currentEmployment;
                        $status = $employment?->employment_status;

                        fputcsv($stream, [
                            $this->safeCsvValue($graduate->student_number),
                            $this->safeCsvValue($graduate->first_name),
                            $this->safeCsvValue($graduate->middle_name),
                            $this->safeCsvValue($graduate->last_name),
                            $this->safeCsvValue($graduate->program_code),
                            $this->safeCsvValue($graduate->batch_year),
                            $this->safeCsvValue(Employment::EMPLOYMENT_STATUSES[$status ?? ''] ?? 'Not reported'),
                            $this->safeCsvValue($this->optionLabel($employment?->sector, Employment::SECTORS)),
                            $this->safeCsvValue($employment?->industry),
                            $this->safeCsvValue($this->optionLabel($employment?->job_level, Employment::JOB_LEVELS)),
                            $this->safeCsvValue($this->optionLabel($employment?->monthly_income, Employment::MONTHLY_INCOME_RANGES)),
                            $this->safeCsvValue($employment?->country),
                            $this->safeCsvValue($employment?->months_to_employment),
                            $this->safeCsvValue($this->optionLabel($employment?->course_relevance, Employment::COURSE_RELEVANCE_OPTIONS)),
                        ], ',', '"', '');
                    }
                });
            } finally {
                fclose($stream);
            }
        }, 'employment-outcomes-details.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /**
     * @return array{program: string, batch: string}
     */
    private function filters(Request $request): array
    {
        $validated = $request->validate([
            'program' => ['nullable', 'string', 'max:64'],
            'batch' => ['nullable', 'integer', 'between:1900,2100'],
        ]);

        return [
            'program' => trim((string) ($validated['program'] ?? '')),
            'batch' => trim((string) ($validated['batch'] ?? '')),
        ];
    }

    /**
     * @param  array{program: string, batch: string}  $filters
     * @return Builder<Graduate>
     */
    private function graduateQuery(array $filters): Builder
    {
        return Graduate::query()
            ->select([
                'id',
                'student_number',
                'first_name',
                'middle_name',
                'last_name',
                'program_code',
                'batch_year',
            ])
            ->with([
                'user:id,student_number',
                'user.profile:id,user_id',
                'user.profile.currentEmployment:id,alumni_profile_id,start_date,employment_status,sector,industry,job_level,monthly_income,country,months_to_employment,course_relevance',
            ])
            ->when($filters['program'] !== '', fn (Builder $query) => $query->where('program_code', $filters['program']))
            ->when($filters['batch'] !== '', fn (Builder $query) => $query->where('batch_year', $filters['batch']));
    }

    /**
     * @param  array{program: string, batch: string}  $filters
     * @return list<array{key: string, label: string, count: int, percentage: string}>
     */
    private function outcomes(array $filters): array
    {
        $labels = [...Employment::EMPLOYMENT_STATUSES, 'not_reported' => 'Not reported'];
        $counts = array_fill_keys(array_keys($labels), 0);

        foreach ($this->graduateQuery($filters)->lazyById(500) as $graduate) {
            $status = $graduate->user?->profile?->currentEmployment?->employment_status;
            $key = is_string($status) && array_key_exists($status, $counts) ? $status : 'not_reported';
            $counts[$key]++;
        }

        $graduateCount = array_sum($counts);
        $outcomes = [];

        foreach ($labels as $key => $label) {
            $count = $counts[$key];
            $outcomes[] = [
                'key' => $key,
                'label' => $label,
                'count' => $count,
                'percentage' => $graduateCount === 0
                    ? '0.0%'
                    : number_format($count / $graduateCount * 100, 1).'%',
            ];
        }

        return $outcomes;
    }

    /**
     * @param  array<string, string>  $options
     */
    private function optionLabel(?string $value, array $options): string
    {
        return $value === null ? '' : ($options[$value] ?? $value);
    }

    private function safeCsvValue(string|int|null $value): string
    {
        $value = (string) ($value ?? '');

        return preg_match('/^[\s\x00-\x1F]*[=+\-@]/u', $value) === 1 ? "'{$value}" : $value;
    }
}
