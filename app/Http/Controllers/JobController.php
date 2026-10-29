<?php

namespace App\Http\Controllers;

use App\Models\Job;
use App\Models\JobApplication;
use Illuminate\Http\Request;
use Illuminate\View\View;

class JobController extends Controller
{
    public function index(Request $request): View
    {
        $typeCounts = $this->employmentTypeCounts();
        $activeType = $this->activeType($request->string('type')->toString(), $typeCounts);

        $jobs = Job::query()
            ->published()
            ->when($activeType !== null, fn ($query) => $query->where('employment_type', $activeType))
            ->with('creator:id,username')
            ->latest('created_at')
            ->latest('id')
            ->paginate(12)
            ->withQueryString();

        return view('jobs.index', [
            'jobs' => $jobs,
            'typeCounts' => $typeCounts,
            'activeType' => $activeType,
            'openRoles' => array_sum($typeCounts),
            'applications' => $request->user()->jobApplications()
                ->whereIn('job_id', $jobs->pluck('id'))
                ->pluck('status', 'job_id'),
            'statusLabels' => JobApplication::STATUS_LABELS,
        ]);
    }

    public function show(Request $request, Job $job): View
    {
        abort_unless($job->status === 'published' && ($job->application_deadline === null || $job->application_deadline->isToday() || $job->application_deadline->isFuture()), 404);

        return view('jobs.show', [
            'job' => $job->load('creator:id,username'),
            'application' => $request->user()->jobApplications()->whereBelongsTo($job)->first(),
            'statusLabels' => JobApplication::STATUS_LABELS,
        ]);
    }

    /**
     * Count the open roles per employment type.
     *
     * @return array<string, int>
     */
    private function employmentTypeCounts(): array
    {
        return Job::query()
            ->published()
            ->select('employment_type')
            ->selectRaw('count(*) as total')
            ->groupBy('employment_type')
            ->orderByDesc('total')
            ->pluck('total', 'employment_type')
            ->all();
    }

    /**
     * @param  array<string, int>  $counts
     */
    private function activeType(string $type, array $counts): ?string
    {
        return $type !== '' && array_key_exists($type, $counts) ? $type : null;
    }
}
