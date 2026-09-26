<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        return view('alumni.profile', [
            'profile' => $request->user()->profile,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'phone' => ['required', 'string', 'max:40'],
            'job_title' => ['required', 'string', 'max:255'],
            'employer' => ['required', 'string', 'max:255'],
            'industry' => ['required', 'string', 'max:255'],
            'employment_status' => ['required', 'in:employed,self-employed,seeking-work,studying,other'],
            'city' => ['required', 'string', 'max:120'],
            'country' => ['required', 'string', 'max:120'],
            'linkedin_url' => ['nullable', 'url', 'max:255'],
            'bio' => ['nullable', 'string', 'max:2000'],
        ]);

        $request->user()->profile()->updateOrCreate([], [
            ...$validated,
            'completed_at' => now(),
        ]);

        return redirect()->route('dashboard')->with('status', 'Your alumni profile has been saved.');
    }
}
