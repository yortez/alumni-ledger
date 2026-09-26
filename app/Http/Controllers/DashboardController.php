<?php

namespace App\Http\Controllers;

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
            return redirect()->route('admin.graduates.index');
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
