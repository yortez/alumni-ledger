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
    public function create(): View
    {
        return view('admin.survey-create', [
            'programs' => Graduate::query()->whereNotNull('program_code')->where('program_code', '<>', '')
                ->distinct()->orderBy('program_code')->pluck('program_code'),
            'graduationYears' => Graduate::query()->whereNotNull('batch_year')
                ->distinct()->orderByDesc('batch_year')->pluck('batch_year'),
        ]);
    }

    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));
        $status = in_array($request->query('status'), ['open', 'draft', 'closed'], true)
            ? (string) $request->query('status')
            : '';
        $query = Survey::query()
            ->with('creator:id,username')
            ->withCount('responses')
            ->when($search !== '', fn ($query) => $query->where('title', 'like', "%{$search}%"))
            ->when($status === 'open', fn ($query) => $query->where('is_active', true)->where(fn ($query) => $query->whereNull('closes_at')->orWhere('closes_at', '>', now())))
            ->when($status === 'draft', fn ($query) => $query->where('is_active', false))
            ->when($status === 'closed', fn ($query) => $query->where('is_active', true)->whereNotNull('closes_at')->where('closes_at', '<=', now()))
            ->latest();

        return view('admin.surveys', [
            'surveys' => $query->paginate(15)->withQueryString(),
            'surveyCount' => (clone $query)->count(),
            'filters' => ['search' => $search, 'status' => $status],
            'programs' => Graduate::query()->whereNotNull('program_code')->where('program_code', '<>', '')
                ->distinct()->orderBy('program_code')->pluck('program_code'),
            'graduationYears' => Graduate::query()->whereNotNull('batch_year')
                ->distinct()->orderByDesc('batch_year')->pluck('batch_year'),
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
            'programs' => Graduate::query()->whereNotNull('program_code')->where('program_code', '<>', '')
                ->distinct()->orderBy('program_code')->pluck('program_code'),
            'graduationYears' => Graduate::query()->whereNotNull('batch_year')
                ->distinct()->orderByDesc('batch_year')->pluck('batch_year'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $questions = $this->validatedQuestions($request);

        $programs = Graduate::query()->whereNotNull('program_code')->pluck('program_code')->unique()->values()->all();
        $graduationYears = Graduate::query()->whereNotNull('batch_year')->pluck('batch_year')->unique()->values()->all();

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:180'],
            'description' => ['nullable', 'string', 'max:5000'],
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
            'questions' => $questions,
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
        $questions = $this->validatedQuestions($request, false);

        $programs = Graduate::query()->whereNotNull('program_code')->pluck('program_code')->unique()->values()->all();
        $graduationYears = Graduate::query()->whereNotNull('batch_year')->pluck('batch_year')->unique()->values()->all();

        $validated = $request->validate([
            'title' => ['sometimes', 'required', 'string', 'max:180'],
            'description' => ['nullable', 'string', 'max:5000'],
            'target_programs' => ['nullable', 'array'],
            'target_programs.*' => ['string', Rule::in($programs)],
            'target_graduation_years' => ['nullable', 'array'],
            'target_graduation_years.*' => ['integer', Rule::in($graduationYears)],
            'closes_at' => ['nullable', 'date', 'after:now'],
            'status' => ['sometimes', 'required', Rule::in(['draft', 'active'])],
        ]);

        if (array_key_exists('title', $validated) || array_key_exists('description', $validated) || $questions !== null || array_key_exists('target_programs', $validated) || array_key_exists('target_graduation_years', $validated) || array_key_exists('closes_at', $validated)) {
            $survey->fill([
                'title' => $validated['title'] ?? $survey->title,
                'description' => $validated['description'] ?? $survey->description,
                'questions' => $questions ?? $survey->questions,
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

    /**
     * @return list<array{prompt: string, section_label: string, type: string, required: bool, options: list<string>}>|null
     */
    private function validatedQuestions(Request $request, bool $required = true): ?array
    {
        if (! $request->exists('questions') && $request->exists('questions_text')) {
            $questionLines = preg_split('/\r\n|\r|\n/', trim((string) $request->input('questions_text'))) ?: [];
            $request->merge([
                'questions' => array_values(array_map(static fn (string $prompt): array => [
                    'prompt' => trim($prompt),
                    'section_label' => '',
                    'type' => 'long_text',
                    'required' => true,
                    'options' => [],
                ], array_filter($questionLines, static fn (string $prompt): bool => trim($prompt) !== ''))),
            ]);
        }

        if (! $request->exists('questions') && ! $required) {
            return null;
        }

        $inputQuestions = $request->input('questions', []);
        $preparedQuestions = [];
        $rules = [
            'questions' => [$required ? 'required' : 'sometimes', 'array', 'min:1', 'max:20'],
        ];

        if (is_array($inputQuestions)) {
            foreach ($inputQuestions as $index => $question) {
                $question = is_array($question) ? $question : [];
                $type = (string) ($question['type'] ?? '');
                $preparedQuestion = [
                    'prompt' => $question['prompt'] ?? null,
                    'section_label' => $question['section_label'] ?? null,
                    'type' => $type,
                    'required' => $question['required'] ?? null,
                ];

                if (in_array($type, Survey::CHOICE_QUESTION_TYPES, true)) {
                    $options = preg_split('/\r\n|\r|\n/', trim((string) ($question['options_text'] ?? ''))) ?: [];
                    $preparedQuestion['options'] = array_values(array_filter(array_map('trim', $options), static fn (string $option): bool => $option !== ''));
                }

                $preparedQuestions[$index] = $preparedQuestion;
                $allowedKeys = in_array($type, Survey::CHOICE_QUESTION_TYPES, true)
                    ? 'prompt,section_label,type,required,options'
                    : 'prompt,section_label,type,required';
                $rules["questions.{$index}"] = ['required', 'array:'.$allowedKeys];
                $rules["questions.{$index}.prompt"] = ['required', 'string', 'max:1000'];
                $rules["questions.{$index}.section_label"] = ['nullable', 'string', 'max:120'];
                $rules["questions.{$index}.type"] = ['required', Rule::in(array_keys(Survey::QUESTION_TYPES))];
                $rules["questions.{$index}.required"] = ['required', 'boolean'];

                if (in_array($type, Survey::CHOICE_QUESTION_TYPES, true)) {
                    $rules["questions.{$index}.options"] = ['required', 'array', 'min:2', 'max:50'];
                    $rules["questions.{$index}.options.*"] = ['required', 'string', 'max:255', 'distinct'];
                }
            }
        }

        $request->merge(['questions' => $preparedQuestions]);
        $validated = $request->validate($rules);

        return array_values(array_map(static fn (array $question): array => [
            'prompt' => $question['prompt'],
            'section_label' => $question['section_label'] ?? '',
            'type' => $question['type'],
            'required' => filter_var($question['required'], FILTER_VALIDATE_BOOLEAN),
            'options' => array_values($question['options'] ?? []),
        ], $validated['questions']));
    }
}
