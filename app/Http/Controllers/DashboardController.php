<?php

namespace App\Http\Controllers;

use App\Enums\AdminRole;
use App\Models\Announcement;
use App\Models\Survey;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View|RedirectResponse
    {
        if ($request->user()->is_admin) {
            $route = match ($request->user()->adminRole()) {
                AdminRole::ContentAdmin => 'admin.announcements.index',
                default => 'admin.graduates.index',
            };

            return redirect()->route($route);
        }

        return view('alumni.dashboard', [
            'profile' => $request->user()->profile,
            'announcements' => Announcement::published()
                ->latest('published_at')
                ->get(),
            'surveys' => Survey::availableTo($request->user())
                ->latest()
                ->get(),
        ]);
    }
}
