<?php

namespace App\Http\Controllers;

use App\Models\Survey;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SurveyController extends Controller
{
    public function index(Request $request): View
    {
        return view('surveys.index', [
            'surveys' => Survey::availableTo($request->user())->latest()->get(),
        ]);
    }

    public function show(Request $request, Survey $survey): View
    {
        $survey = Survey::availableTo($request->user())->findOrFail($survey->id);

        return view('surveys.show', ['survey' => $survey]);
    }

    public function storeResponse(Request $request, Survey $survey): RedirectResponse
    {
        $survey = Survey::availableTo($request->user())->findOrFail($survey->id);
        $questions = $survey->normalizedQuestions();
        $questionKeys = array_keys($questions);
        $rules = ['answers' => ['nullable', 'array:'.implode(',', $questionKeys)]];

        foreach ($questions as $index => $question) {
            $requiredRule = $question['required'] ? 'required' : 'nullable';

            if ($question['type'] === 'checkbox') {
                $rules["answers.{$index}"] = [$question['required'] ? 'required' : 'sometimes', 'array', ...($question['required'] ? ['min:1'] : [])];
                $rules["answers.{$index}.*"] = ['required', 'string', Rule::in($question['options'])];

                continue;
            }

            $rules["answers.{$index}"] = match ($question['type']) {
                'radio', 'dropdown' => [$requiredRule, 'string', Rule::in($question['options'])],
                'short_text' => [$requiredRule, 'string', 'max:1000'],
                default => [$requiredRule, 'string', 'max:5000'],
            };
        }

        $validated = $request->validate($rules);
        $answers = [];

        foreach ($questions as $index => $question) {
            $answer = $validated['answers'][$index] ?? ($question['type'] === 'checkbox' ? [] : '');
            $answers[$index] = $question['type'] === 'checkbox' ? array_values($answer) : $answer;
        }

        $survey->responses()->create([
            'user_id' => $request->user()->id,
            'answers' => $answers,
            'submitted_at' => now(),
        ]);

        return redirect()->route('surveys.index')->with('status', 'Your survey response has been submitted.');
    }
}
