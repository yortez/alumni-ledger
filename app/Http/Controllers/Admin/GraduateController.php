<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AlumniProfile;
use App\Models\Graduate;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class GraduateController extends Controller
{
    public function create(): View
    {
        return view('admin.graduate-create');
    }

    public function index(Request $request): View
    {
        $filters = $this->graduateFilters($request);
        $graduates = $this->graduateQuery($filters)
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->paginate(20)
            ->withQueryString();

        return view('admin.graduates', [
            'graduates' => $graduates,
            'search' => $filters['search'],
            'filters' => $filters,
            'programs' => Graduate::query()->whereNotNull('program_code')->distinct()->orderBy('program_code')->pluck('program_code'),
            'batches' => Graduate::query()->whereNotNull('batch_year')->distinct()->orderByDesc('batch_year')->pluck('batch_year'),
            'graduateCount' => Graduate::query()->count(),
            'claimedCount' => User::query()->whereNotNull('student_number')->count(),
            'profileCount' => AlumniProfile::query()->whereNotNull('completed_at')->count(),
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        $filters = $this->graduateFilters($request);

        return response()->streamDownload(function () use ($filters): void {
            $stream = fopen('php://output', 'w');

            if (! is_resource($stream)) {
                return;
            }

            try {
                fputcsv($stream, ['Alumni', 'Student number', 'Program', 'Batch', 'Contact', 'Employment', 'Verification'], ',', '"', '');

                $this->graduateQuery($filters)
                    ->orderBy('id')
                    ->chunk(500, function ($graduates) use ($stream): void {
                        foreach ($graduates as $graduate) {
                            $profile = $graduate->user?->profile;
                            $employment = $profile?->employments->first();

                            fputcsv($stream, [
                                $this->safeCsvValue($graduate->fullName()),
                                $this->safeCsvValue($graduate->student_number),
                                $this->safeCsvValue($graduate->program_code),
                                $this->safeCsvValue($graduate->batch_year),
                                $this->safeCsvValue($graduate->user?->email),
                                $this->safeCsvValue($employment ? $employment->job_title.' · '.$employment->employer : null),
                                $graduate->user ? 'Verified' : 'Not registered',
                            ], ',', '"', '');
                        }
                    });
            } finally {
                fclose($stream);
            }
        }, 'alumni-records.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function downloadImportTemplate(): StreamedResponse
    {
        return response()->streamDownload(function (): void {
            $stream = fopen('php://output', 'w');

            if (! is_resource($stream)) {
                return;
            }

            fputcsv($stream, [
                'student_number',
                'first_name',
                'last_name',
                'middle_name',
                'program_code',
                'batch_year',
                'latin_honor',
            ], ',', '"', '');

            fclose($stream);
        }, 'alumni-import-template.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /**
     * @return array{search: string, program: string, batch: string, status: string}
     */
    private function graduateFilters(Request $request): array
    {
        $status = (string) $request->query('status', '');

        return [
            'search' => trim((string) $request->query('search', '')),
            'program' => trim((string) $request->query('program', '')),
            'batch' => trim((string) $request->query('batch', '')),
            'status' => in_array($status, ['verified', 'unregistered'], true) ? $status : '',
        ];
    }

    /**
     * @param  array{search: string, program: string, batch: string, status: string}  $filters
     */
    private function graduateQuery(array $filters): Builder
    {
        return Graduate::query()
            ->with([
                'user:id,student_number,email',
                'user.profile:id,user_id,phone,completed_at',
                'user.profile.employments' => fn ($query) => $query->orderByDesc('is_current')->orderByDesc('start_date'),
            ])
            ->when($filters['search'] !== '', function (Builder $query) use ($filters): void {
                $search = $filters['search'];
                $query->where(function (Builder $query) use ($search): void {
                    $query->where('student_number', 'like', "%{$search}%")
                        ->orWhere('first_name', 'like', "%{$search}%")
                        ->orWhere('middle_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhereHas('user', fn (Builder $userQuery) => $userQuery->where('email', 'like', "%{$search}%"));
                });
            })
            ->when($filters['program'] !== '', fn (Builder $query) => $query->where('program_code', $filters['program']))
            ->when($filters['batch'] !== '', fn (Builder $query) => $query->where('batch_year', $filters['batch']))
            ->when($filters['status'] === 'verified', fn (Builder $query) => $query->whereHas('user'))
            ->when($filters['status'] === 'unregistered', fn (Builder $query) => $query->whereDoesntHave('user'));
    }

    private function safeCsvValue(string|int|null $value): string
    {
        $value = (string) ($value ?? '');

        return preg_match('/^[\s\x00-\x1F]*[=+\-@]/u', $value) === 1 ? "'{$value}" : $value;
    }

    public function show(Graduate $graduate): View
    {
        $graduate->load([
            'user',
            'user.profile.employments',
            'user.surveyResponses.survey',
        ]);

        return view('admin.graduate-detail', [
            'graduate' => $graduate,
            'applicant' => $graduate->user,
            'profile' => $graduate->user?->profile,
        ]);
    }

    public function file(Graduate $graduate, string $file): Response
    {
        $profile = $graduate->user?->profile;
        $path = match ($file) {
            'photo' => $profile?->profile_photo_path,
            'diploma' => $profile?->diploma_document_path,
            default => null,
        };

        abort_unless($path && Storage::disk('local')->exists($path), 404);

        if ($file === 'photo') {
            return response()->file(Storage::disk('local')->path($path));
        }

        return Storage::disk('local')->download($path, 'diploma-tor.'.pathinfo($path, PATHINFO_EXTENSION));
    }

    public function edit(Graduate $graduate): View
    {
        return view('admin.graduate-edit', [
            'graduate' => $graduate,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'student_number' => ['required', 'string', 'max:64', 'unique:graduates,student_number'],
            'first_name' => ['required', 'string', 'max:255'],
            'middle_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'program_code' => ['nullable', 'string', 'max:64'],
            'batch_year' => ['nullable', 'integer', 'between:1900,2100'],
            'latin_honor' => ['required', Rule::in(Graduate::LATIN_HONORS)],
        ]);

        Graduate::query()->create($validated);

        return redirect()->route('admin.graduates.index')->with('status', 'Graduate added to the master list.');
    }

    public function update(Request $request, Graduate $graduate): RedirectResponse
    {
        $validated = $request->validate([
            'student_number' => ['required', 'string', 'max:64', Rule::unique('graduates', 'student_number')->ignore($graduate->id)],
            'first_name' => ['required', 'string', 'max:255'],
            'middle_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'program_code' => ['nullable', 'string', 'max:64'],
            'batch_year' => ['nullable', 'integer', 'between:1900,2100'],
            'latin_honor' => ['required', Rule::in(Graduate::LATIN_HONORS)],
        ]);

        $graduate->update($validated);

        return redirect()->route('admin.graduates.index')->with('status', 'Graduate record updated.');
    }

    public function import(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt', 'max:5120'],
        ]);

        $file = new \SplFileObject($request->file('file')->getRealPath());
        $file->setFlags(\SplFileObject::READ_CSV | \SplFileObject::SKIP_EMPTY);
        $headers = $file->fgetcsv();

        if (! is_array($headers)) {
            return back()->withErrors(['file' => 'The CSV file is empty.']);
        }

        $headers[0] = preg_replace('/^\xEF\xBB\xBF/', '', (string) $headers[0]);
        $headers = array_map(fn ($header) => strtolower(trim((string) $header)), $headers);

        if (! in_array('student_number', $headers, true) || ! in_array('first_name', $headers, true) || ! in_array('last_name', $headers, true)) {
            return back()->withErrors(['file' => 'CSV headers must include student_number, first_name, and last_name.']);
        }

        $rows = [];
        $rowNumber = 1;
        while (! $file->eof()) {
            $values = $file->fgetcsv();
            $rowNumber++;

            if (! is_array($values) || count(array_filter($values, fn ($value) => trim((string) $value) !== '')) === 0) {
                continue;
            }

            $row = array_combine($headers, array_pad(array_slice($values, 0, count($headers)), count($headers), null));
            $row = [
                'student_number' => trim((string) ($row['student_number'] ?? '')),
                'first_name' => trim((string) ($row['first_name'] ?? '')),
                'middle_name' => trim((string) ($row['middle_name'] ?? '')) ?: null,
                'last_name' => trim((string) ($row['last_name'] ?? '')),
                'program_code' => trim((string) ($row['program_code'] ?? '')) ?: null,
                'batch_year' => trim((string) ($row['batch_year'] ?? '')) ?: null,
                'latin_honor' => trim((string) ($row['latin_honor'] ?? '')) ?: 'none',
            ];

            $validator = Validator::make($row, [
                'student_number' => ['required', 'string', 'max:64'],
                'first_name' => ['required', 'string', 'max:255'],
                'middle_name' => ['nullable', 'string', 'max:255'],
                'last_name' => ['required', 'string', 'max:255'],
                'program_code' => ['nullable', 'string', 'max:64'],
                'batch_year' => ['nullable', 'integer', 'between:1900,2100'],
                'latin_honor' => ['required', Rule::in(Graduate::LATIN_HONORS)],
            ]);

            if ($validator->fails()) {
                return back()->withErrors([
                    'file' => "CSV row {$rowNumber} is invalid. Check the student number, name fields, batch year, and Latin honor.",
                ]);
            }

            $rows[$row['student_number']] = $row;

            if (count($rows) > 5000) {
                return back()->withErrors(['file' => 'A single import may contain at most 5,000 graduates.']);
            }
        }

        if ($rows === []) {
            return back()->withErrors(['file' => 'The CSV contains no graduate records.']);
        }

        DB::transaction(function () use ($rows): void {
            Graduate::query()->upsert(
                array_values($rows),
                ['student_number'],
                ['first_name', 'middle_name', 'last_name', 'program_code', 'batch_year', 'latin_honor', 'updated_at'],
            );
        });

        return redirect()->route('admin.graduates.index')->with('status', count($rows).' graduate records imported.');
    }
}
