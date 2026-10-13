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

    <section class="mt-12 border-t border-line pt-9">
        <div class="mb-6 flex flex-col justify-between gap-3 sm:flex-row sm:items-end">
            <div><p class="eyebrow">Career timeline</p><h2 class="font-display mt-2 text-3xl">Employment history</h2></div>
            <p class="text-sm text-muted">Add current and previous roles to your profile.</p>
        </div>
        @if (session('employment_status'))
            <p class="mb-5 border-l-2 border-forest bg-mint px-4 py-3 text-sm text-forest">{{ session('employment_status') }}</p>
        @endif

        <div class="grid gap-5">
            @foreach ($employmentRecords as $employment)
                <article class="border border-line bg-white p-5 sm:p-6">
                    <div class="mb-5 flex flex-col justify-between gap-3 sm:flex-row sm:items-start">
                        <div>
                            <h3 class="font-display text-xl">{{ $employment->job_title }}</h3>
                            <p class="mt-1 text-sm text-muted">{{ $employment->employer }}{{ $employment->location ? ' · '.$employment->location : '' }}</p>
                            <p class="mt-2 text-xs font-semibold uppercase tracking-wide text-forest">{{ $employment->is_current ? 'Current role' : 'Past role' }}</p>
                        </div>
                        <p class="text-sm text-muted">{{ $employment->start_date->format('M Y') }} – {{ $employment->is_current ? 'Present' : ($employment->end_date?->format('M Y') ?? 'Date not provided') }}</p>
                    </div>
                    <form method="POST" action="{{ route('profile.employments.update', $employment) }}">
                        @csrf @method('PATCH')
                        <div class="grid gap-x-5 gap-y-4 sm:grid-cols-2">
                            <div class="field"><label for="job_title_{{ $employment->id }}">Job title</label><input id="job_title_{{ $employment->id }}" name="job_title" value="{{ $employment->job_title }}" required>@error('job_title')<p class="field-error">{{ $message }}</p>@enderror</div>
                            <div class="field"><label for="employer_{{ $employment->id }}">Employer</label><input id="employer_{{ $employment->id }}" name="employer" value="{{ $employment->employer }}" required>@error('employer')<p class="field-error">{{ $message }}</p>@enderror</div>
                            <div class="field"><label for="location_{{ $employment->id }}">Location <span class="font-normal text-muted">Optional</span></label><input id="location_{{ $employment->id }}" name="location" value="{{ $employment->location }}">@error('location')<p class="field-error">{{ $message }}</p>@enderror</div>
                            <div class="field"><label for="start_date_{{ $employment->id }}">Start date</label><input id="start_date_{{ $employment->id }}" type="date" name="start_date" value="{{ $employment->start_date->toDateString() }}" required>@error('start_date')<p class="field-error">{{ $message }}</p>@enderror</div>
                            <div class="field"><label for="end_date_{{ $employment->id }}">End date <span class="font-normal text-muted">Leave blank for current roles</span></label><input id="end_date_{{ $employment->id }}" type="date" name="end_date" value="{{ $employment->end_date?->toDateString() }}">@error('end_date')<p class="field-error">{{ $message }}</p>@enderror</div>
                            <div class="field sm:col-span-2"><input type="hidden" name="is_current" value="0"><label class="inline-flex items-center gap-2"><input type="checkbox" name="is_current" value="1" @checked($employment->is_current)> This is my current role</label>@error('is_current')<p class="field-error">{{ $message }}</p>@enderror</div>
                            <div class="field sm:col-span-2"><label for="description_{{ $employment->id }}">Description <span class="font-normal text-muted">Optional</span></label><textarea id="description_{{ $employment->id }}" name="description" rows="3" maxlength="2000">{{ $employment->description }}</textarea>@error('description')<p class="field-error">{{ $message }}</p>@enderror</div>
                        </div>
                        <div class="mt-5 flex flex-wrap items-center justify-between gap-3 border-t border-line pt-4">
                            <button type="submit" class="bg-forest px-4 py-2.5 text-sm font-semibold text-white hover:bg-forest-dark">Save changes</button>
                    </form>
                            <form method="POST" action="{{ route('profile.employments.destroy', $employment) }}">@csrf @method('DELETE')<button type="submit" class="text-sm font-semibold text-red-700 underline underline-offset-4 hover:text-red-900">Remove role</button></form>
                        </div>
                </article>
            @endforeach
        </div>

        <form method="POST" action="{{ route('profile.employments.store') }}" class="mt-8 border border-line bg-white p-5 sm:p-6">
            @csrf
            <h3 class="font-display text-xl">Add employment</h3>
            <div class="mt-5 grid gap-x-5 gap-y-4 sm:grid-cols-2">
                <div class="field"><label for="new_job_title">Job title</label><input id="new_job_title" name="job_title" value="{{ old('job_title') }}" required>@error('job_title')<p class="field-error">{{ $message }}</p>@enderror</div>
                <div class="field"><label for="new_employer">Employer</label><input id="new_employer" name="employer" value="{{ old('employer') }}" required>@error('employer')<p class="field-error">{{ $message }}</p>@enderror</div>
                <div class="field"><label for="new_location">Location <span class="font-normal text-muted">Optional</span></label><input id="new_location" name="location" value="{{ old('location') }}">@error('location')<p class="field-error">{{ $message }}</p>@enderror</div>
                <div class="field"><label for="new_start_date">Start date</label><input id="new_start_date" type="date" name="start_date" value="{{ old('start_date') }}" required>@error('start_date')<p class="field-error">{{ $message }}</p>@enderror</div>
                <div class="field"><label for="new_end_date">End date <span class="font-normal text-muted">Leave blank for current roles</span></label><input id="new_end_date" type="date" name="end_date" value="{{ old('end_date') }}">@error('end_date')<p class="field-error">{{ $message }}</p>@enderror</div>
                <div class="field"><input type="hidden" name="is_current" value="0"><label class="inline-flex items-center gap-2"><input type="checkbox" name="is_current" value="1" @checked(old('is_current'))> This is my current role</label>@error('is_current')<p class="field-error">{{ $message }}</p>@enderror</div>
                <div class="field sm:col-span-2"><label for="new_description">Description <span class="font-normal text-muted">Optional</span></label><textarea id="new_description" name="description" rows="3" maxlength="2000">{{ old('description') }}</textarea>@error('description')<p class="field-error">{{ $message }}</p>@enderror</div>
            </div>
            <button type="submit" class="mt-5 bg-forest px-5 py-3 text-sm font-semibold text-white hover:bg-forest-dark">Add employment</button>
        </form>
    </section>
</div>
@endsection
