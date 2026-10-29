<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\Graduate;
use App\Models\Job;
use App\Models\Survey;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): RedirectResponse|View
    {
        $user = $request->user();

        if ($user->is_admin) {
            return redirect()->route('filament.admin.pages.dashboard');
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
            'jobs' => Job::query()
                ->published()
                ->latest('created_at')
                ->latest('id')
                ->limit(3)
                ->get(),
        ]);
    }
}
