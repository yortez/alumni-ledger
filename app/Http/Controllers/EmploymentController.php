<?php

namespace App\Http\Controllers;

use App\Models\Employment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class EmploymentController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->rules());
        $profile = $request->user()->profile()->firstOrCreate([]);

        $profile->employments()->create([
            ...$validated,
            'end_date' => $validated['is_current'] ? null : ($validated['end_date'] ?? null),
        ]);

        return redirect()->route('profile.edit')->with('employment_status', 'Employment record added.');
    }

    public function update(Request $request, Employment $employment): RedirectResponse
    {
        $ownedEmployment = $this->findEmploymentForUser($request, $employment);
        $validated = $request->validate($this->rules());

        $ownedEmployment->update([
            ...$validated,
            'end_date' => $validated['is_current'] ? null : ($validated['end_date'] ?? null),
        ]);

        return redirect()->route('profile.edit')->with('employment_status', 'Employment record updated.');
    }

    public function destroy(Request $request, Employment $employment): RedirectResponse
    {
        $this->findEmploymentForUser($request, $employment)->delete();

        return redirect()->route('profile.edit')->with('employment_status', 'Employment record removed.');
    }

    private function findEmploymentForUser(Request $request, Employment $employment): Employment
    {
        return $request->user()->profile?->employments()
            ->whereKey($employment->getKey())
            ->firstOrFail() ?? abort(404);
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    private function rules(): array
    {
        return [
            'employment_status' => ['required', Rule::in(array_keys(Employment::EMPLOYMENT_STATUSES))],
            'sector' => ['nullable', Rule::in(array_keys(Employment::SECTORS))],
            'job_title' => ['required', 'string', 'max:255'],
            'employer' => ['required', 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
            'country' => ['nullable', 'string', 'max:120'],
            'industry' => ['nullable', 'string', 'max:255'],
            'job_level' => ['nullable', Rule::in(array_keys(Employment::JOB_LEVELS))],
            'monthly_income' => ['nullable', Rule::in(array_keys(Employment::MONTHLY_INCOME_RANGES))],
            'months_to_employment' => ['nullable', 'integer', 'between:0,600'],
            'course_relevance' => ['nullable', Rule::in(array_keys(Employment::COURSE_RELEVANCE_OPTIONS))],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'is_current' => ['required', 'boolean'],
            'description' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
