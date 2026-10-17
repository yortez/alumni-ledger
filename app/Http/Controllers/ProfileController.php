<?php

namespace App\Http\Controllers;

use App\Models\Graduate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use RuntimeException;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        $profile = $request->user()->profile;
        $profile?->load('employments');
        $graduate = Graduate::query()
            ->where('student_number', $request->user()->student_number)
            ->first();

        return view('alumni.profile', [
            'profile' => $profile,
            'graduate' => $graduate,
            'employmentRecords' => $profile?->employments ?? collect(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'middle_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'suffix' => ['nullable', Rule::in(['Jr.', 'Sr.', 'II', 'III', 'IV'])],
            'birthdate' => ['nullable', 'date', 'before_or_equal:today'],
            'sex' => ['nullable', Rule::in(['male', 'female', 'other', 'prefer_not_to_say'])],
            'civil_status' => ['nullable', Rule::in(['single', 'married', 'widowed', 'divorced', 'separated'])],
            'phone' => ['required', 'string', 'max:40'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user)],
            'current_address' => ['nullable', 'string', 'max:2000'],
            'permanent_address' => ['nullable', 'string', 'max:2000'],
            'profile_photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,gif', 'extensions:jpg,jpeg,png,gif', 'max:5120'],
            'diploma_document' => ['nullable', 'file', 'mimes:pdf,doc,docx', 'extensions:pdf,doc,docx', 'max:5120'],
        ]);

        $profileData = $validated;
        unset($profileData['email'], $profileData['profile_photo'], $profileData['diploma_document']);

        $name = implode(' ', array_filter([
            $validated['first_name'],
            $validated['middle_name'] ?? null,
            $validated['last_name'],
            $validated['suffix'] ?? null,
        ]));

        $existingProfile = $user->profile()->first();
        $storedPaths = [];

        try {
            foreach ([
                'profile_photo' => 'profile_photo_path',
                'diploma_document' => 'diploma_document_path',
            ] as $inputName => $pathColumn) {
                if (! $request->hasFile($inputName)) {
                    continue;
                }

                $path = $request->file($inputName)->store('alumni-profiles/'.$user->id, 'local');

                if (! is_string($path)) {
                    throw new RuntimeException('The uploaded file could not be stored.');
                }

                $storedPaths[$pathColumn] = $path;
            }

            DB::transaction(function () use ($user, $validated, $profileData, $name, $storedPaths): void {
                if ($user->email !== $validated['email']) {
                    $user->email_verified_at = null;
                }

                $user->name = $name;
                $user->email = $validated['email'];
                $user->save();

                $user->profile()->updateOrCreate([], [
                    ...$profileData,
                    ...$storedPaths,
                    'completed_at' => now(),
                ]);
            });
        } catch (Throwable $exception) {
            Storage::disk('local')->delete(array_values($storedPaths));

            throw $exception;
        }

        $oldPaths = [];

        foreach ($storedPaths as $pathColumn => $newPath) {
            $oldPath = $existingProfile?->{$pathColumn};

            if ($oldPath && $oldPath !== $newPath) {
                $oldPaths[] = $oldPath;
            }
        }

        Storage::disk('local')->delete($oldPaths);

        return redirect()->route('dashboard')->with('status', 'Your alumni profile has been saved.');
    }

    public function file(Request $request, string $file): Response
    {
        $profile = $request->user()->profile()->first();
        $path = match ($file) {
            'photo' => $profile?->profile_photo_path,
            'diploma' => $profile?->diploma_document_path,
            default => null,
        };

        abort_unless($path && Storage::disk('local')->exists($path), 404);

        if ($file === 'photo') {
            return response()->file(Storage::disk('local')->path($path));
        }

        return Storage::disk('local')->download($path, 'diploma-tor.'.pathinfo($path, PATHINFO_EXTENSION));
    }
}
