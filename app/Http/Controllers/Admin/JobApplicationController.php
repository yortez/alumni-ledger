<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Job;
use Illuminate\View\View;

class JobApplicationController extends Controller
{
    public function index(Job $job): View
    {
        return view('admin.job-applications', [
            'job' => $job,
            'applications' => $job->applications()
                ->with('user:id,name,username,email')
                ->latest('submitted_at')
                ->paginate(25),
        ]);
    }
}
