@extends('layouts.app')

@section('title', 'Alumni profile')

@section('content')
<div class="mx-auto max-w-5xl px-5 py-10 sm:px-8 lg:py-14">
    <div class="mb-8 border-b border-line pb-7"><p class="eyebrow">Your details</p><h1 class="font-display mt-3 text-4xl">Alumni profile</h1><p class="mt-3 text-sm text-muted">{{ auth()->user()->name }} · {{ auth()->user()->student_number }}</p></div>
    <form method="POST" action="{{ route('profile.update') }}" class="form-surface">@csrf @method('PUT')
        <div class="grid gap-x-6 gap-y-5 sm:grid-cols-2">
            <div class="field"><label for="phone">Phone</label><input id="phone" name="phone" value="{{ old('phone', $profile?->phone) }}" autocomplete="tel" required>@error('phone')<p class="field-error">{{ $message }}</p>@enderror</div>
            <div class="field"><label for="employment_status">Employment status</label><select id="employment_status" name="employment_status" required><option value="">Select status</option>@foreach (['employed' => 'Employed', 'self-employed' => 'Self-employed', 'seeking-work' => 'Seeking work', 'studying' => 'Studying', 'other' => 'Other'] as $value => $label)<option value="{{ $value }}" @selected(old('employment_status', $profile?->employment_status) === $value)>{{ $label }}</option>@endforeach</select>@error('employment_status')<p class="field-error">{{ $message }}</p>@enderror</div>
            <div class="field"><label for="job_title">Job title</label><input id="job_title" name="job_title" value="{{ old('job_title', $profile?->job_title) }}" required>@error('job_title')<p class="field-error">{{ $message }}</p>@enderror</div>
            <div class="field"><label for="employer">Employer</label><input id="employer" name="employer" value="{{ old('employer', $profile?->employer) }}" required>@error('employer')<p class="field-error">{{ $message }}</p>@enderror</div>
            <div class="field"><label for="industry">Industry</label><input id="industry" name="industry" value="{{ old('industry', $profile?->industry) }}" required>@error('industry')<p class="field-error">{{ $message }}</p>@enderror</div>
            <div class="field"><label for="city">City</label><input id="city" name="city" value="{{ old('city', $profile?->city) }}" autocomplete="address-level2" required>@error('city')<p class="field-error">{{ $message }}</p>@enderror</div>
            <div class="field"><label for="country">Country</label><input id="country" name="country" value="{{ old('country', $profile?->country) }}" autocomplete="country-name" required>@error('country')<p class="field-error">{{ $message }}</p>@enderror</div>
            <div class="field"><label for="linkedin_url">LinkedIn URL <span class="font-normal text-muted">Optional</span></label><input id="linkedin_url" type="url" name="linkedin_url" value="{{ old('linkedin_url', $profile?->linkedin_url) }}" autocomplete="url">@error('linkedin_url')<p class="field-error">{{ $message }}</p>@enderror</div>
            <div class="field sm:col-span-2"><label for="bio">A little about you <span class="font-normal text-muted">Optional</span></label><textarea id="bio" name="bio" rows="4" maxlength="2000">{{ old('bio', $profile?->bio) }}</textarea>@error('bio')<p class="field-error">{{ $message }}</p>@enderror</div>
        </div>
        <div class="mt-8 flex flex-col-reverse justify-between gap-4 border-t border-line pt-6 sm:flex-row sm:items-center"><a href="{{ route('dashboard') }}" class="text-sm font-medium text-muted hover:text-ink">Back to dashboard</a><button type="submit" class="inline-flex items-center justify-between gap-10 bg-forest px-5 py-3.5 text-sm font-semibold text-white hover:bg-forest-dark">Save profile <span aria-hidden="true">&#8599;</span></button></div>
    </form>
</div>
@endsection
