<?php

namespace App\Http\Controllers;

use App\Models\Graduate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
        $validated = $request->validate([
            'phone' => ['required', 'string', 'max:40'],
            'city' => ['required', 'string', 'max:120'],
            'country' => ['required', 'string', 'max:120'],
        ]);

        $request->user()->profile()->updateOrCreate([], [
            ...$validated,
            'completed_at' => now(),
        ]);

        return redirect()->route('dashboard')->with('status', 'Your alumni profile has been saved.');
    }
}
