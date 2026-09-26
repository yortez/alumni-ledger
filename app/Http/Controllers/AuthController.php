<?php

namespace App\Http\Controllers;

use App\Models\Graduate;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function create(): View
    {
        return view('auth.register');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'student_number' => ['required', 'string', 'max:64', 'exists:graduates,student_number', 'unique:users,student_number'],
            'username' => ['required', 'string', 'max:40', 'alpha_dash', 'unique:users,username'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'student_number.exists' => 'We could not find that student number in the graduate master list.',
            'student_number.unique' => 'An account has already been created for that student number.',
        ]);

        $user = DB::transaction(function () use ($validated): User {
            $graduate = Graduate::query()
                ->where('student_number', $validated['student_number'])
                ->lockForUpdate()
                ->firstOrFail();

            return User::query()->create([
                'name' => $graduate->name,
                'student_number' => $graduate->student_number,
                'username' => $validated['username'],
                'email' => $validated['email'],
                'password' => $validated['password'],
            ]);
        });

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('dashboard');
    }

    public function login(): View
    {
        return view('auth.login');
    }

    public function authenticate(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($credentials)) {
            return back()->withErrors([
                'email' => 'Those credentials did not match our records.',
            ])->onlyInput('email');
        }

        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}
