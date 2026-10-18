<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\Graduate;
use App\Models\Job;
use App\Models\JobApplication;
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
            'profile' => $user->profile,
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
        ]);
    }

    private function adminDashboard(User $user): View
    {
        $adminRole = $user->adminRole();
        abort_if($adminRole === null, 403);

        $metrics = [];

        if ($adminRole->hasPermission('graduates')) {
            $metrics[] = ['label' => 'Graduate records', 'value' => Graduate::query()->count(), 'detail' => 'Entries in the master list', 'icon' => 'graduates', 'tone' => 'cyan'];
            $metrics[] = ['label' => 'Accounts claimed', 'value' => User::query()->whereNotNull('student_number')->count(), 'detail' => 'Graduates with an account', 'icon' => 'verified', 'tone' => 'green'];
        }

        if ($adminRole->hasPermission('surveys')) {
            $metrics[] = ['label' => 'Open surveys', 'value' => $this->openSurveyCount(), 'detail' => 'Accepting responses', 'icon' => 'survey', 'tone' => 'amber'];
        }
        if ($adminRole->hasPermission('jobs')) {
            $metrics[] = ['label' => 'Active jobs', 'value' => Job::published()->count(), 'detail' => 'Currently accepting applications', 'icon' => 'building', 'tone' => 'rose'];
            // $metrics[] = ['label' => 'Applications to review', 'value' => JobApplication::query()->whereIn('status', ['submitted', 'reviewing'])->count(), 'detail' => 'Submitted or under review', 'icon' => 'briefcase', 'tone' => 'orange'];
        }

        $workspaceDefinitions = [
            'manage_admins' => ['title' => 'Administrator access', 'detail' => 'Create administrators and assign their roles.', 'route' => 'admin.users.index'],
            'manage_roles' => ['title' => 'Role definitions', 'detail' => 'Create roles and choose their module permissions.', 'route' => 'admin.roles.index'],
            'graduates' => ['title' => 'Open master list', 'detail' => 'Search, update, and import graduate records.', 'route' => 'admin.graduates.index'],
            'announcements' => ['title' => 'Open content manager', 'detail' => 'Create and publish announcements for alumni.', 'route' => 'admin.announcements.index'],
            'surveys' => ['title' => 'Manage surveys', 'detail' => 'Set audiences, review responses, and close surveys.', 'route' => 'admin.surveys.index'],
            'jobs' => ['title' => 'Manage jobs', 'detail' => 'Publish opportunities and review applications.', 'route' => 'admin.jobs.index'],
        ];
        $actions = [];

        foreach ($workspaceDefinitions as $permission => $workspace) {
            if ($adminRole->hasPermission($permission)) {
                $actions[] = $workspace;
            }
        }

        return view('admin.dashboard', [
            'adminRole' => $adminRole,
            'metrics' => $metrics,
            'actions' => $actions,
        ]);
    }

    private function openSurveyCount(): int
    {
        return Survey::query()
            ->where('is_active', true)
            ->where(fn ($query) => $query->whereNull('closes_at')->orWhere('closes_at', '>', now()))
            ->count();
    }
}
