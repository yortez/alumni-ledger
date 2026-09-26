<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Graduate;
use App\Models\Job;
use App\Models\JobApplication;
use App\Notifications\JobApplicationStatusUpdated;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class JobApplicationController extends Controller
{
    public const STATUS_LABELS = [
        'submitted' => 'Submitted',
        'reviewing' => 'Under review',
        'interview_scheduled' => 'Interview scheduled',
        'approved' => 'Approved',
        'rejected' => 'Rejected',
    ];

    public function index(Job $job): View
    {
        return view('admin.job-applications', [
            'job' => $job,
            'applications' => $job->applications()
                ->with(['user:id,name,username,email,student_number', 'user.profile'])
                ->latest('submitted_at')
                ->paginate(25),
            'statusLabels' => self::STATUS_LABELS,
        ]);
    }

    public function show(Job $job, JobApplication $application): View
    {
        abort_unless($application->job_id === $job->id, 404);

        $application->load(['user.profile']);
        $graduate = $application->user?->student_number
            ? Graduate::query()->where('student_number', $application->user->student_number)->first()
            : null;

        return view('admin.job-application-detail', [
            'job' => $job,
            'application' => $application,
            'graduate' => $graduate,
            'profile' => $application->user?->profile,
            'statusLabels' => self::STATUS_LABELS,
        ]);
    }

    public function update(Request $request, Job $job, JobApplication $application): RedirectResponse
    {
        abort_unless($application->job_id === $job->id, 404);

        $validated = $request->validate([
            'status' => ['required', Rule::in(array_keys(self::STATUS_LABELS))],
        ]);

        $application->update(['status' => $validated['status']]);

        $application->user->notify(new JobApplicationStatusUpdated($job, $application));

        return redirect()->route('admin.jobs.applications.index', $job)
            ->with('status', 'Application status updated to '.self::STATUS_LABELS[$validated['status']].'.');
    }
}
