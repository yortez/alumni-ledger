<?php

namespace App\Http\Controllers;

use App\Models\Survey;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
        $questionKeys = array_keys($survey->questions);
        $validated = $request->validate([
            'answers' => ['required', 'array:'.implode(',', $questionKeys), 'size:'.count($questionKeys)],
            ...collect($questionKeys)->mapWithKeys(fn (int $index): array => [
                "answers.{$index}" => ['required', 'string', 'max:5000'],
            ])->all(),
        ]);

        $survey->responses()->create([
            'user_id' => $request->user()->id,
            'answers' => $validated['answers'],
            'submitted_at' => now(),
        ]);

        return redirect()->route('surveys.index')->with('status', 'Your survey response has been submitted.');
    }
}
