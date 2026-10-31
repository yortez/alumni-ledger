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
    private const MINIMUM_REPORT_GROUP_SIZE = 5;

    public function index(Request $request): View
    {
        $filters = $this->filters($request);
        $canExportAggregate = $request->user()->hasAdminPermission('employment_reports');
        $analytics = $canExportAggregate ? $this->analytics($filters) : null;
        $reportSuppressed = $analytics !== null && $analytics['graduate_count'] < self::MINIMUM_REPORT_GROUP_SIZE;

        if ($reportSuppressed) {
            $analytics = null;
        }

        return view('admin.employment-reports', [
            'programs' => Graduate::query()->whereNotNull('program_code')->distinct()->orderBy('program_code')->pluck('program_code'),
            'batches' => Graduate::query()->whereNotNull('batch_year')->distinct()->orderByDesc('batch_year')->pluck('batch_year'),
            'filters' => $filters,
            'analytics' => $analytics,
            'outcomes' => $analytics['outcomes'] ?? [],
            'graduateCount' => $analytics['graduate_count'] ?? null,
            'reportSuppressed' => $reportSuppressed,
            'canExportAggregate' => $canExportAggregate,
            'canExportDetails' => $request->user()->hasAdminPermission('employment_report_details'),
        ]);
    }

    public function exportSummary(Request $request): StreamedResponse
    {
        $filters = $this->filters($request);
        $analytics = $this->analytics($filters);

        return response()->streamDownload(function () use ($analytics): void {
            $stream = fopen('php://output', 'w');

            if (! is_resource($stream)) {
                return;
            }

            try {
                if ($analytics['graduate_count'] < self::MINIMUM_REPORT_GROUP_SIZE) {
                    fputcsv($stream, ['Report', 'Results'], ',', '"', '');
                    fputcsv($stream, ['Suppressed', 'Minimum cohort size is 5 graduates.'], ',', '"', '');

                    return;
                }

                fputcsv($stream, ['Metric', 'Category', 'Count or value', 'Percentage', 'Denominator'], ',', '"', '');

                foreach ($this->summaryRows($analytics) as $row) {
                    fputcsv($stream, [
                        $this->safeCsvValue($row['metric']),
                        $this->safeCsvValue($row['category']),
                        $row['value'],
                        $row['percentage'],
                        $row['denominator'],
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
     * @return array{
     *     graduate_count: int,
     *     reported_status_count: int,
     *     employed_count: int,
     *     reporting_coverage: array{count: int, denominator: int, percentage: string},
     *     employment_rate: array{count: int, denominator: int, percentage: string},
     *     time_to_employment: array{median_months: float|null, sample_count: int},
     *     outcomes: list<array{key: string, label: string, count: int, percentage: string}>,
     *     distributions: array<string, array{label: string, denominator: int, items: list<array{key: string, label: string, count: int, percentage: string}>}>
     * }
     */
    private function analytics(array $filters): array
    {
        $labels = [...Employment::EMPLOYMENT_STATUSES, 'not_reported' => 'Not reported'];
        $outcomeCounts = array_fill_keys(array_keys($labels), 0);
        $distributionDefinitions = [
            'sector' => ['label' => 'Sector', 'options' => Employment::SECTORS],
            'monthly_income' => ['label' => 'Monthly income', 'options' => Employment::MONTHLY_INCOME_RANGES],
            'course_relevance' => ['label' => 'Course relevance', 'options' => Employment::COURSE_RELEVANCE_OPTIONS],
        ];
        $distributionCounts = [];

        foreach ($distributionDefinitions as $key => $definition) {
            $distributionCounts[$key] = array_fill_keys(array_keys($definition['options']), 0);
        }

        $employedStatuses = ['employed_local', 'employed_abroad', 'self_employed'];
        $graduateCount = 0;
        $reportedStatusCount = 0;
        $employedCount = 0;
        $monthsToEmployment = [];

        foreach ($this->graduateQuery($filters)->lazyById(500) as $graduate) {
            $graduateCount++;
            $employment = $graduate->user?->profile?->currentEmployment;
            $status = $employment?->employment_status;
            $statusKey = is_string($status) && array_key_exists($status, Employment::EMPLOYMENT_STATUSES)
                ? $status
                : 'not_reported';
            $outcomeCounts[$statusKey]++;

            if ($statusKey === 'not_reported') {
                continue;
            }

            $reportedStatusCount++;

            if (! in_array($statusKey, $employedStatuses, true)) {
                continue;
            }

            $employedCount++;

            foreach ($distributionDefinitions as $key => $definition) {
                $value = $employment->{$key};

                if (is_string($value) && array_key_exists($value, $distributionCounts[$key])) {
                    $distributionCounts[$key][$value]++;
                }
            }

            if (is_numeric($employment->months_to_employment)) {
                $monthsToEmployment[] = (float) $employment->months_to_employment;
            }
        }

        $outcomes = [];

        foreach ($labels as $key => $label) {
            $count = $outcomeCounts[$key];
            $outcomes[] = [
                'key' => $key,
                'label' => $label,
                'count' => $count,
                'percentage' => $this->percentage($count, $graduateCount),
            ];
        }

        $distributions = [];

        foreach ($distributionDefinitions as $key => $definition) {
            $items = [];

            foreach ($definition['options'] as $optionKey => $label) {
                $count = $distributionCounts[$key][$optionKey];

                if ($count > 0) {
                    $items[] = [
                        'key' => $optionKey,
                        'label' => $label,
                        'count' => $count,
                        'percentage' => $this->percentage($count, $employedCount),
                    ];
                }
            }

            $notReportedCount = $employedCount - array_sum($distributionCounts[$key]);

            if ($notReportedCount > 0) {
                $items[] = [
                    'key' => 'not_reported',
                    'label' => 'Not reported',
                    'count' => $notReportedCount,
                    'percentage' => $this->percentage($notReportedCount, $employedCount),
                ];
            }

            $distributions[$key] = [
                'label' => $definition['label'],
                'denominator' => $employedCount,
                'items' => $items,
            ];
        }

        sort($monthsToEmployment, SORT_NUMERIC);
        $monthsSampleCount = count($monthsToEmployment);
        $medianMonths = null;

        if ($monthsSampleCount > 0) {
            $middle = intdiv($monthsSampleCount, 2);
            $medianMonths = $monthsSampleCount % 2 === 0
                ? ($monthsToEmployment[$middle - 1] + $monthsToEmployment[$middle]) / 2
                : $monthsToEmployment[$middle];
        }

        return [
            'graduate_count' => $graduateCount,
            'reported_status_count' => $reportedStatusCount,
            'employed_count' => $employedCount,
            'reporting_coverage' => [
                'count' => $reportedStatusCount,
                'denominator' => $graduateCount,
                'percentage' => $this->percentage($reportedStatusCount, $graduateCount),
            ],
            'employment_rate' => [
                'count' => $employedCount,
                'denominator' => $reportedStatusCount,
                'percentage' => $this->percentage($employedCount, $reportedStatusCount),
            ],
            'time_to_employment' => [
                'median_months' => $medianMonths,
                'sample_count' => $monthsSampleCount,
            ],
            'outcomes' => $outcomes,
            'distributions' => $distributions,
        ];
    }

    /**
     * @param  array{
     *     graduate_count: int,
     *     reported_status_count: int,
     *     employed_count: int,
     *     reporting_coverage: array{count: int, denominator: int, percentage: string},
     *     employment_rate: array{count: int, denominator: int, percentage: string},
     *     time_to_employment: array{median_months: float|null, sample_count: int},
     *     outcomes: list<array{key: string, label: string, count: int, percentage: string}>,
     *     distributions: array<string, array{label: string, denominator: int, items: list<array{key: string, label: string, count: int, percentage: string}>}>
     * }  $analytics
     * @return list<array{metric: string, category: string, value: string|int, percentage: string, denominator: int}>
     */
    private function summaryRows(array $analytics): array
    {
        $rows = [
            [
                'metric' => 'Reporting coverage',
                'category' => 'Outcome status reported',
                'value' => $analytics['reporting_coverage']['count'],
                'percentage' => $analytics['reporting_coverage']['percentage'],
                'denominator' => $analytics['reporting_coverage']['denominator'],
            ],
            [
                'metric' => 'Employment rate among respondents',
                'category' => 'Employed outcomes',
                'value' => $analytics['employment_rate']['count'],
                'percentage' => $analytics['employment_rate']['percentage'],
                'denominator' => $analytics['employment_rate']['denominator'],
            ],
            [
                'metric' => 'Median months to employment',
                'category' => 'Months',
                'value' => $analytics['time_to_employment']['median_months'] === null
                    ? 'Not reported'
                    : number_format($analytics['time_to_employment']['median_months'], 1, '.', ''),
                'percentage' => '',
                'denominator' => $analytics['time_to_employment']['sample_count'],
            ],
        ];

        foreach ($analytics['outcomes'] as $outcome) {
            $rows[] = [
                'metric' => 'Employment outcomes',
                'category' => $outcome['label'],
                'value' => $outcome['count'],
                'percentage' => $outcome['percentage'],
                'denominator' => $analytics['graduate_count'],
            ];
        }

        foreach ($analytics['distributions'] as $distribution) {
            foreach ($distribution['items'] as $item) {
                $rows[] = [
                    'metric' => $distribution['label'],
                    'category' => $item['label'],
                    'value' => $item['count'],
                    'percentage' => $item['percentage'],
                    'denominator' => $distribution['denominator'],
                ];
            }
        }

        return $rows;
    }

    private function percentage(int $count, int $denominator): string
    {
        return $denominator === 0 ? '0.0%' : number_format($count / $denominator * 100, 1).'%';
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
