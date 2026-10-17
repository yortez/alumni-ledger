<?php

namespace App\Http\Controllers;

use App\Models\Graduate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        $profile = $request->user()->profile;
        $profile?->load('employments');
        $graduate = Graduate::query()
            ->where('student_number', $request->user()->student_number)
            ->first();

        return view('alumni.profile', [
            'profile' => $profile,
            'graduate' => $graduate,
            'employmentRecords' => $profile?->employments ?? collect(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'middle_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'suffix' => ['nullable', Rule::in(['Jr.', 'Sr.', 'II', 'III', 'IV'])],
            'birthdate' => ['nullable', 'date', 'before_or_equal:today'],
            'sex' => ['nullable', Rule::in(['male', 'female', 'other', 'prefer_not_to_say'])],
            'civil_status' => ['nullable', Rule::in(['single', 'married', 'widowed', 'divorced', 'separated'])],
            'phone' => ['required', 'string', 'max:40'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user)],
            'current_address' => ['nullable', 'string', 'max:2000'],
            'permanent_address' => ['nullable', 'string', 'max:2000'],
        ]);

        $profileData = $validated;
        unset($profileData['email']);

        $name = implode(' ', array_filter([
            $validated['first_name'],
            $validated['middle_name'] ?? null,
            $validated['last_name'],
            $validated['suffix'] ?? null,
        ]));

        DB::transaction(function () use ($user, $validated, $profileData, $name): void {
            if ($user->email !== $validated['email']) {
                $user->email_verified_at = null;
            }

            $user->name = $name;
            $user->email = $validated['email'];
            $user->save();

            $user->profile()->updateOrCreate([], [
                ...$profileData,
                'completed_at' => now(),
            ]);
        });

        return redirect()->route('dashboard')->with('status', 'Your alumni profile has been saved.');
    }
}
