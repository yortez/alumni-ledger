<?php

namespace App\Http\Controllers;

use App\Models\AdminRole;
use App\Models\AlumniProfile;
use App\Models\Announcement;
use App\Models\Graduate;
use App\Models\Job;
use App\Models\Survey;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();

        if ($user->is_admin) {
            return $this->adminDashboard($user);
        }

        return view('alumni.dashboard', [
            'profile' => $user->profile?->load('currentEmployment'),
            'graduate' => Graduate::query()
                ->where('student_number', $user->student_number)
                ->first(),
            'announcements' => Announcement::published()
                ->orderByDesc('is_pinned')
                ->latest('published_at')
                ->get(),
            'surveys' => Survey::availableTo($user)
                ->latest()
                ->get(),
            'jobs' => Job::query()
                ->published()
                ->latest('created_at')
                ->latest('id')
                ->limit(3)
                ->get(),
        ]);
    }

    private function adminDashboard(User $user): View
    {
        $adminRole = $user->adminRole();
        abort_if($adminRole === null, 403);

        return view('admin.dashboard', [
            'adminRole' => $adminRole,
            'metrics' => $this->adminMetrics($adminRole),
            'workspaces' => $this->adminWorkspaces($adminRole),
        ]);
    }

    /**
     * Collect the measures the signed-in role is allowed to see.
     *
     * @return array<string, int>
     */
    private function adminMetrics(AdminRole $adminRole): array
    {
        $metrics = [];

        if ($adminRole->hasPermission('graduates')) {
            $metrics['graduateRecords'] = Graduate::query()->count();
            $metrics['accountsClaimed'] = User::query()->whereNotNull('student_number')->count();
            $metrics['applicantProfiles'] = AlumniProfile::query()->count();
        }

        if ($adminRole->hasPermission('announcements')) {
            $metrics['publishedAnnouncements'] = Announcement::published()->count();
        }

        if ($adminRole->hasPermission('surveys')) {
            $metrics['openSurveys'] = $this->openSurveyCount();
        }

        if ($adminRole->hasPermission('jobs')) {
            $metrics['activeJobs'] = Job::published()->count();
        }

        return $metrics;
    }

    /**
     * Collect the modules the signed-in role can open.
     *
     * @return array<string, array{title: string, detail: string, route: string}>
     */
    private function adminWorkspaces(AdminRole $adminRole): array
    {
        $definitions = [
            'manage_admins' => ['title' => 'Administrator accounts', 'detail' => 'Create administrators and assign their roles.', 'route' => 'admin.users.index'],
            'manage_roles' => ['title' => 'Role definitions', 'detail' => 'Create roles and choose their module permissions.', 'route' => 'admin.roles.index'],
            'graduates' => ['title' => 'Open master list', 'detail' => 'Search, update, and import graduate records.', 'route' => 'admin.graduates.index'],
            'employment_reports' => ['title' => 'View employment reports', 'detail' => 'Review cohort employment outcomes and authorized exports.', 'route' => 'admin.reports.employment.index'],
            'announcements' => ['title' => 'Manage announcements', 'detail' => 'Create and publish announcements for alumni.', 'route' => 'admin.announcements.index'],
            'surveys' => ['title' => 'Manage surveys', 'detail' => 'Set audiences, review responses, and close surveys.', 'route' => 'admin.surveys.index'],
            'jobs' => ['title' => 'Manage jobs', 'detail' => 'Publish opportunities and review applications.', 'route' => 'admin.jobs.index'],
        ];

        $workspaces = array_filter(
            $definitions,
            static fn (string $permission): bool => $adminRole->hasPermission($permission),
            ARRAY_FILTER_USE_KEY,
        );

        if ($adminRole->hasPermission('employment_report_details')) {
            $workspaces['employment_reports'] = $definitions['employment_reports'];
        }

        return $workspaces;
    }

    private function openSurveyCount(): int
    {
        return Survey::query()
            ->where('is_active', true)
            ->where(fn ($query) => $query->whereNull('closes_at')->orWhere('closes_at', '>', now()))
            ->count();
    }
}
