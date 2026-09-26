<?php

use App\Http\Controllers\Admin\AnnouncementController;
use App\Http\Controllers\Admin\GraduateController;
use App\Http\Controllers\Admin\SurveyController as AdminSurveyController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\JobApplicationController;
use App\Http\Controllers\JobController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SurveyController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('home');
})->name('home');

Route::middleware('guest')->group(function (): void {
    Route::get('/register', [AuthController::class, 'create'])->name('register');
    Route::post('/register', [AuthController::class, 'store'])->middleware('throttle:5,1')->name('register.store');
    Route::get('/login', [AuthController::class, 'login'])->name('login');
    Route::post('/login', [AuthController::class, 'authenticate'])->middleware('throttle:5,1')->name('login.store');
});

Route::middleware('auth')->group(function (): void {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/logout', [AuthController::class, 'destroy'])->name('logout');
    Route::get('/surveys', [SurveyController::class, 'index'])->name('surveys.index');
    Route::get('/surveys/{survey}', [SurveyController::class, 'show'])->name('surveys.show');
    Route::post('/surveys/{survey}/responses', [SurveyController::class, 'storeResponse'])->name('surveys.responses.store');
    Route::get('/jobs', [JobController::class, 'index'])->name('jobs.index');
    Route::get('/jobs/{job}', [JobController::class, 'show'])->name('jobs.show');
    Route::post('/jobs/{job}/applications', [JobApplicationController::class, 'store'])->name('jobs.applications.store');
});

Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function (): void {
    Route::get('/announcements/{announcement}/edit', [AnnouncementController::class, 'edit'])->name('announcements.edit');
    Route::resource('announcements', AnnouncementController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::get('/surveys/{survey}/edit', [AdminSurveyController::class, 'edit'])->name('surveys.edit');
    Route::resource('surveys', AdminSurveyController::class)->only(['index', 'show', 'store', 'update', 'destroy']);
    Route::get('/graduates', [GraduateController::class, 'index'])->name('graduates.index');
    Route::get('/graduates/{graduate}/edit', [GraduateController::class, 'edit'])->name('graduates.edit');
    Route::get('/graduates/{graduate}', [GraduateController::class, 'show'])->name('graduates.show');
    Route::post('/graduates', [GraduateController::class, 'store'])->name('graduates.store');
    Route::post('/graduates/import', [GraduateController::class, 'import'])->name('graduates.import');
    Route::patch('/graduates/{graduate}', [GraduateController::class, 'update'])->name('graduates.update');
    Route::get('/jobs', [App\Http\Controllers\Admin\JobController::class, 'index'])->name('jobs.index');
    Route::get('/jobs/{job}/edit', [App\Http\Controllers\Admin\JobController::class, 'edit'])->name('jobs.edit');
    Route::post('/jobs', [App\Http\Controllers\Admin\JobController::class, 'store'])->name('jobs.store');
    Route::patch('/jobs/{job}', [App\Http\Controllers\Admin\JobController::class, 'update'])->name('jobs.update');
    Route::delete('/jobs/{job}', [App\Http\Controllers\Admin\JobController::class, 'destroy'])->name('jobs.destroy');
    Route::get('/jobs/{job}/applications', [App\Http\Controllers\Admin\JobApplicationController::class, 'index'])->name('jobs.applications.index');
    Route::get('/jobs/{job}/applications/{application}', [App\Http\Controllers\Admin\JobApplicationController::class, 'show'])->name('jobs.applications.show');
    Route::patch('/jobs/{job}/applications/{application}', [App\Http\Controllers\Admin\JobApplicationController::class, 'update'])->name('jobs.applications.update');
});
