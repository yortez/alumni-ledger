<?php

namespace App\Http\Controllers;

use App\Enums\AdminRole;
use App\Models\AlumniProfile;
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
            'announcements' => Announcement::published()
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

        $metrics = match ($adminRole) {
            AdminRole::RecordsAdmin => [
                ['label' => 'Graduate records', 'value' => Graduate::query()->count(), 'detail' => 'Entries in the master list'],
                ['label' => 'Accounts claimed', 'value' => User::query()->whereNotNull('student_number')->count(), 'detail' => 'Graduates with an account'],
                ['label' => 'Applicant profiles', 'value' => AlumniProfile::query()->whereNotNull('completed_at')->count(), 'detail' => 'Profiles completed'],
            ],
            AdminRole::ContentAdmin => [
                ['label' => 'Published announcements', 'value' => Announcement::published()->count(), 'detail' => 'Visible to alumni'],
                ['label' => 'Open surveys', 'value' => $this->openSurveyCount(), 'detail' => 'Accepting responses'],
                ['label' => 'Active jobs', 'value' => Job::published()->count(), 'detail' => 'Currently accepting applications'],
                ['label' => 'Applications to review', 'value' => JobApplication::query()->whereIn('status', ['submitted', 'reviewing'])->count(), 'detail' => 'Submitted or under review'],
            ],
            AdminRole::SuperAdmin => [
                ['label' => 'Administrator accounts', 'value' => User::query()->where('is_admin', true)->count(), 'detail' => 'Accounts with administrative access'],
                ['label' => 'Graduate records', 'value' => Graduate::query()->count(), 'detail' => 'Entries in the master list'],
                ['label' => 'Published announcements', 'value' => Announcement::published()->count(), 'detail' => 'Visible to alumni'],
                ['label' => 'Open surveys', 'value' => $this->openSurveyCount(), 'detail' => 'Accepting responses'],
                ['label' => 'Active jobs', 'value' => Job::published()->count(), 'detail' => 'Currently accepting applications'],
            ],
        };

        $actions = match ($adminRole) {
            AdminRole::RecordsAdmin => [
                ['title' => 'Open master list', 'detail' => 'Search, update, and import graduate records.', 'route' => 'admin.graduates.index'],
            ],
            AdminRole::ContentAdmin => [
                ['title' => 'Open content manager', 'detail' => 'Create and publish announcements for alumni.', 'route' => 'admin.announcements.index'],
                ['title' => 'Manage surveys', 'detail' => 'Set audiences, review responses, and close surveys.', 'route' => 'admin.surveys.index'],
                ['title' => 'Manage jobs', 'detail' => 'Publish opportunities and review applications.', 'route' => 'admin.jobs.index'],
            ],
            AdminRole::SuperAdmin => [
                ['title' => 'Administrator access', 'detail' => 'Create administrators and assign their roles.', 'route' => 'admin.users.index'],
                ['title' => 'Open master list', 'detail' => 'Search, update, and import graduate records.', 'route' => 'admin.graduates.index'],
                ['title' => 'Open content manager', 'detail' => 'Manage announcements, surveys, jobs, and applications.', 'route' => 'admin.announcements.index'],
                ['title' => 'Manage surveys', 'detail' => 'Set audiences, review responses, and close surveys.', 'route' => 'admin.surveys.index'],
                ['title' => 'Manage jobs', 'detail' => 'Publish opportunities and review applications.', 'route' => 'admin.jobs.index'],
            ],
        };

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
