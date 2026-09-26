<?php

namespace App\Http\Controllers;

use App\Models\Job;
use Illuminate\Http\Request;
use Illuminate\View\View;

class JobController extends Controller
{
    public function index(): View
    {
        return view('jobs.index', [
            'jobs' => Job::query()
                ->published()
                ->with('creator:id,username')
                ->latest('created_at')
                ->latest('id')
                ->paginate(12),
        ]);
    }

    public function show(Request $request, Job $job): View
    {
        abort_unless($job->status === 'published' && ($job->application_deadline === null || $job->application_deadline->isToday() || $job->application_deadline->isFuture()), 404);

        return view('jobs.show', [
            'job' => $job->load('creator:id,username'),
            'application' => $request->user()->jobApplications()->whereBelongsTo($job)->first(),
        ]);
    }
}
