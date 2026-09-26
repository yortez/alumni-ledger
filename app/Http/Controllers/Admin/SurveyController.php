<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Graduate;
use App\Models\Survey;
use App\Models\User;
use App\Notifications\SurveyPublished;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SurveyController extends Controller
{
    public function index(): View
    {
        return view('admin.surveys', [
            'surveys' => Survey::query()
                ->with('creator:id,username')
                ->withCount('responses')
                ->latest()
                ->paginate(15),
            'programs' => Graduate::query()->whereNotNull('program')->where('program', '<>', '')
                ->distinct()->orderBy('program')->pluck('program'),
            'graduationYears' => Graduate::query()->whereNotNull('graduation_year')
                ->distinct()->orderByDesc('graduation_year')->pluck('graduation_year'),
        ]);
    }

    public function show(Survey $survey): View
    {
        return view('admin.survey-responses', [
            'survey' => $survey,
            'responses' => $survey->responses()
                ->with('user:id,name,username,student_number')
                ->latest('submitted_at')
                ->paginate(25),
        ]);
    }

    public function edit(Survey $survey): View
    {
        return view('admin.survey-edit', [
            'survey' => $survey,
            'programs' => Graduate::query()->whereNotNull('program')->where('program', '<>', '')
                ->distinct()->orderBy('program')->pluck('program'),
            'graduationYears' => Graduate::query()->whereNotNull('graduation_year')
                ->distinct()->orderByDesc('graduation_year')->pluck('graduation_year'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $questionLines = preg_split('/\r\n|\r|\n/', trim($request->string('questions_text')->toString())) ?: [];
        $request->merge([
            'questions' => array_values(array_filter(array_map('trim', $questionLines), fn (string $question): bool => $question !== '')),
        ]);

        $programs = Graduate::query()->whereNotNull('program')->pluck('program')->unique()->values()->all();
        $graduationYears = Graduate::query()->whereNotNull('graduation_year')->pluck('graduation_year')->unique()->values()->all();

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:180'],
            'description' => ['nullable', 'string', 'max:5000'],
            'questions' => ['required', 'array', 'min:1', 'max:20'],
            'questions.*' => ['required', 'string', 'max:1000'],
            'target_programs' => ['nullable', 'array'],
            'target_programs.*' => ['string', Rule::in($programs)],
            'target_graduation_years' => ['nullable', 'array'],
            'target_graduation_years.*' => ['integer', Rule::in($graduationYears)],
            'closes_at' => ['nullable', 'date', 'after:now'],
            'status' => ['required', Rule::in(['draft', 'active'])],
        ]);

        $survey = new Survey([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'questions' => array_values($validated['questions']),
            'target_programs' => array_values($validated['target_programs'] ?? []),
            'target_graduation_years' => array_map('intval', array_values($validated['target_graduation_years'] ?? [])),
            'is_active' => $validated['status'] === 'active',
            'closes_at' => $validated['closes_at'] ?? null,
        ]);
        $survey->creator()->associate($request->user());
        $survey->save();

        if ($validated['status'] === 'active') {
            User::query()->where('is_admin', false)->each(function (User $user) use ($survey): void {
                if ($survey->availableTo($user)->whereKey($survey->id)->exists()) {
                    $user->notify(new SurveyPublished($survey));
                }
            });
        }

        return redirect()->route('admin.surveys.index')->with('status', 'Survey created.');
    }

    public function update(Request $request, Survey $survey): RedirectResponse
    {
        $questionLines = preg_split('/\r\n|\r|\n/', trim((string) $request->input('questions_text', '')));
        $request->merge([
            'questions' => array_values(array_filter(array_map('trim', $questionLines ?: []), fn (string $question): bool => $question !== '')),
        ]);

        $programs = Graduate::query()->whereNotNull('program')->pluck('program')->unique()->values()->all();
        $graduationYears = Graduate::query()->whereNotNull('graduation_year')->pluck('graduation_year')->unique()->values()->all();

        $validated = $request->validate([
            'title' => ['sometimes', 'required', 'string', 'max:180'],
            'description' => ['nullable', 'string', 'max:5000'],
            'questions' => ['sometimes', 'required', 'array', 'min:1', 'max:20'],
            'questions.*' => ['required', 'string', 'max:1000'],
            'target_programs' => ['nullable', 'array'],
            'target_programs.*' => ['string', Rule::in($programs)],
            'target_graduation_years' => ['nullable', 'array'],
            'target_graduation_years.*' => ['integer', Rule::in($graduationYears)],
            'closes_at' => ['nullable', 'date', 'after:now'],
            'status' => ['sometimes', 'required', Rule::in(['draft', 'active'])],
        ]);

        if (array_key_exists('title', $validated) || array_key_exists('description', $validated) || array_key_exists('questions', $validated) || array_key_exists('target_programs', $validated) || array_key_exists('target_graduation_years', $validated) || array_key_exists('closes_at', $validated)) {
            $survey->fill([
                'title' => $validated['title'] ?? $survey->title,
                'description' => $validated['description'] ?? $survey->description,
                'questions' => array_values($validated['questions'] ?? $survey->questions),
                'target_programs' => array_values($validated['target_programs'] ?? $survey->target_programs),
                'target_graduation_years' => array_map('intval', array_values($validated['target_graduation_years'] ?? $survey->target_graduation_years)),
                'closes_at' => $validated['closes_at'] ?? $survey->closes_at,
            ]);
        }

        if (array_key_exists('status', $validated)) {
            $survey->is_active = $validated['status'] === 'active';
        }

        $survey->save();

        if (($validated['status'] ?? ($survey->is_active ? 'active' : 'draft')) === 'active') {
            User::query()->where('is_admin', false)->each(function (User $user) use ($survey): void {
                if ($survey->availableTo($user)->whereKey($survey->id)->exists()) {
                    $user->notify(new SurveyPublished($survey));
                }
            });
        }

        return redirect()->route('admin.surveys.index')->with('status', 'Survey updated.');
    }

    public function destroy(Survey $survey): RedirectResponse
    {
        $survey->delete();

        return back()->with('status', 'Survey deleted.');
    }
}
