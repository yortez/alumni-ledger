<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Job;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class JobController extends Controller
{
    public function index(): View
    {
        return view('admin.jobs', [
            'jobs' => Job::query()->with('creator:id,username')->withCount('applications')->latest()->paginate(15),
            'publishedCount' => Job::query()->where('status', 'published')->count(),
        ]);
    }

    public function edit(Job $job): View
    {
        return view('admin.job-edit', [
            'job' => $job,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:180'],
            'company' => ['required', 'string', 'max:180'],
            'location' => ['required', 'string', 'max:180'],
            'employment_type' => ['required', 'string', 'max:80'],
            'description' => ['required', 'string', 'max:10000'],
            'requirements' => ['nullable', 'string', 'max:10000'],
            'application_deadline' => ['nullable', 'date', 'after_or_equal:today'],
            'status' => ['required', Rule::in(['draft', 'published'])],
        ]);

        $job = new Job($validated);
        $job->creator()->associate($request->user());
        $job->save();

        return redirect()->route('admin.jobs.index')->with('status', 'Job posting created.');
    }

    public function update(Request $request, Job $job): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['sometimes', 'required', 'string', 'max:180'],
            'company' => ['sometimes', 'required', 'string', 'max:180'],
            'location' => ['sometimes', 'required', 'string', 'max:180'],
            'employment_type' => ['sometimes', 'required', 'string', 'max:80'],
            'description' => ['sometimes', 'required', 'string', 'max:10000'],
            'requirements' => ['nullable', 'string', 'max:10000'],
            'application_deadline' => ['nullable', 'date', 'after_or_equal:today'],
            'status' => ['sometimes', 'required', Rule::in(['draft', 'published'])],
        ]);

        $job->fill([
            'title' => $validated['title'] ?? $job->title,
            'company' => $validated['company'] ?? $job->company,
            'location' => $validated['location'] ?? $job->location,
            'employment_type' => $validated['employment_type'] ?? $job->employment_type,
            'description' => $validated['description'] ?? $job->description,
            'requirements' => $validated['requirements'] ?? $job->requirements,
            'application_deadline' => $validated['application_deadline'] ?? $job->application_deadline,
        ]);

        if (array_key_exists('status', $validated)) {
            $job->status = $validated['status'];
        }

        $job->save();

        return redirect()->route('admin.jobs.index')->with('status', 'Job posting updated.');
    }

    public function destroy(Job $job): RedirectResponse
    {
        $job->delete();

        return back()->with('status', 'Job posting deleted.');
    }
}
