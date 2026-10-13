<?php

namespace App\Http\Controllers;

use App\Models\Employment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

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
     * @return array<string, array<int, string>>
     */
    private function rules(): array
    {
        return [
            'job_title' => ['required', 'string', 'max:255'],
            'employer' => ['required', 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'is_current' => ['required', 'boolean'],
            'description' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
