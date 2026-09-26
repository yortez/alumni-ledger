<?php

namespace App\Http\Controllers;

use App\Models\Job;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class JobApplicationController extends Controller
{
    public function store(Request $request, Job $job): RedirectResponse
    {
        abort_unless($job->status === 'published' && ($job->application_deadline === null || $job->application_deadline->isToday() || $job->application_deadline->isFuture()), 404);

        if (! $request->user()->profile?->completed_at) {
            return redirect()->route('profile.edit')->withErrors([
                'application' => 'Complete your alumni profile before submitting an application.',
            ]);
        }

        $validated = $request->validate([
            'cover_letter' => ['nullable', 'string', 'max:5000'],
        ]);

        if ($request->user()->jobApplications()->whereBelongsTo($job)->exists()) {
            return back()->withErrors(['application' => 'You have already applied for this role.']);
        }

        $job->applications()->create([
            'user_id' => $request->user()->id,
            'cover_letter' => $validated['cover_letter'] ?? null,
            'submitted_at' => now(),
        ]);

        return redirect()->route('jobs.show', $job)->with('status', 'Your application has been submitted.');
    }
}
