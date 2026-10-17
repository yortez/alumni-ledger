<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AlumniProfile;
use App\Models\Graduate;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class GraduateController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));
        $graduates = Graduate::query()
            ->with([
                'user:id,student_number',
                'user.profile.employments' => fn ($query) => $query->orderByDesc('is_current')->orderByDesc('start_date'),
            ])
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('student_number', 'like', "%{$search}%")
                        ->orWhere('first_name', 'like', "%{$search}%")
                        ->orWhere('middle_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%");
                });
            })
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->paginate(20)
            ->withQueryString();

        return view('admin.graduates', [
            'graduates' => $graduates,
            'search' => $search,
            'graduateCount' => Graduate::query()->count(),
            'claimedCount' => User::query()->whereNotNull('student_number')->count(),
            'profileCount' => AlumniProfile::query()->whereNotNull('completed_at')->count(),
        ]);
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

        return back()->with('status', 'Graduate added to the master list.');
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

        return back()->with('status', count($rows).' graduate records imported.');
    }
}
