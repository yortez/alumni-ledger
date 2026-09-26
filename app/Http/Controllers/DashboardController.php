<?php

namespace App\Http\Controllers;

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
        ]);
    }
}
