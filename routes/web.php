<?php

use App\Http\Controllers\Admin\GraduateController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
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
    Route::post('/logout', [AuthController::class, 'destroy'])->name('logout');
});

Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function (): void {
    Route::get('/graduates', [GraduateController::class, 'index'])->name('graduates.index');
    Route::post('/graduates', [GraduateController::class, 'store'])->name('graduates.store');
    Route::post('/graduates/import', [GraduateController::class, 'import'])->name('graduates.import');
});
