<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AlumniProfile;
use App\Models\Graduate;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class GraduateController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));
        $graduates = Graduate::query()
            ->with(['user:id,student_number', 'user.profile'])
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('student_number', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%");
                });
            })
            ->orderBy('name')
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
        $graduate->load(['user', 'user.profile']);

        return view('admin.graduate-detail', [
            'graduate' => $graduate,
            'applicant' => $graduate->user,
            'profile' => $graduate->user?->profile,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'student_number' => ['required', 'string', 'max:64', 'unique:graduates,student_number'],
            'name' => ['required', 'string', 'max:255'],
            'program' => ['nullable', 'string', 'max:255'],
            'graduation_year' => ['nullable', 'integer', 'between:1900,2100'],
        ]);

        Graduate::query()->create($validated);

        return back()->with('status', 'Graduate added to the master list.');
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

        if (! in_array('student_number', $headers, true) || ! in_array('name', $headers, true)) {
            return back()->withErrors(['file' => 'CSV headers must include student_number and name.']);
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
                'name' => trim((string) ($row['name'] ?? '')),
                'program' => trim((string) ($row['program'] ?? '')) ?: null,
                'graduation_year' => trim((string) ($row['graduation_year'] ?? '')) ?: null,
            ];

            $validator = Validator::make($row, [
                'student_number' => ['required', 'string', 'max:64'],
                'name' => ['required', 'string', 'max:255'],
                'program' => ['nullable', 'string', 'max:255'],
                'graduation_year' => ['nullable', 'integer', 'between:1900,2100'],
            ]);

            if ($validator->fails()) {
                return back()->withErrors([
                    'file' => "CSV row {$rowNumber} is invalid. Check the student number, name, and graduation year.",
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
                ['name', 'program', 'graduation_year', 'updated_at'],
            );
        });

        return back()->with('status', count($rows).' graduate records imported.');
    }
}
