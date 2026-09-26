@extends('layouts.app')

@section('title', 'Edit job posting')

@section('content')
<div class="mx-auto max-w-3xl px-5 py-10 sm:px-8 lg:py-14">
    <div class="mb-6 flex items-center justify-between gap-4 border-b border-line pb-6">
        <div>
            <p class="eyebrow">Administration</p>
            <h1 class="font-display mt-3 text-4xl sm:text-5xl">Edit job posting</h1>
        </div>
        <a href="{{ route('admin.jobs.index') }}" class="text-sm font-semibold text-forest underline underline-offset-4">Back to jobs</a>
    </div>

    <section class="border border-line bg-white p-5 sm:p-6">
        <form method="POST" action="{{ route('admin.jobs.update', $job) }}" class="grid gap-5">
            @csrf
            @method('PATCH')

            <div class="field">
                <label for="title">Job title</label>
                <input id="title" name="title" maxlength="180" value="{{ old('title', $job->title) }}" required>
                @error('title')<p class="field-error">{{ $message }}</p>@enderror
            </div>

            <div class="field">
                <label for="company">Company</label>
                <input id="company" name="company" maxlength="180" value="{{ old('company', $job->company) }}" required>
                @error('company')<p class="field-error">{{ $message }}</p>@enderror
            </div>

            <div class="field">
                <label for="location">Location</label>
                <input id="location" name="location" maxlength="180" value="{{ old('location', $job->location) }}" required>
                @error('location')<p class="field-error">{{ $message }}</p>@enderror
            </div>

            <div class="field">
                <label for="employment_type">Employment type</label>
                <input id="employment_type" name="employment_type" maxlength="80" value="{{ old('employment_type', $job->employment_type) }}" required>
                @error('employment_type')<p class="field-error">{{ $message }}</p>@enderror
            </div>

            <div class="field">
                <label for="description">Description</label>
                <textarea id="description" name="description" rows="6" maxlength="10000" required>{{ old('description', $job->description) }}</textarea>
                @error('description')<p class="field-error">{{ $message }}</p>@enderror
            </div>

            <div class="field">
                <label for="requirements">Requirements <span class="font-normal text-muted">Optional</span></label>
                <textarea id="requirements" name="requirements" rows="5" maxlength="10000">{{ old('requirements', $job->requirements) }}</textarea>
                @error('requirements')<p class="field-error">{{ $message }}</p>@enderror
            </div>

            <div class="field">
                <label for="application_deadline">Application deadline <span class="font-normal text-muted">Optional</span></label>
                <input id="application_deadline" name="application_deadline" type="date" min="{{ today()->toDateString() }}" value="{{ old('application_deadline', $job->application_deadline?->toDateString()) }}">
                @error('application_deadline')<p class="field-error">{{ $message }}</p>@enderror
            </div>

            <div class="field">
                <label for="status">Status</label>
                <select id="status" name="status" required>
                    <option value="published" @selected(old('status', $job->status) === 'published')>Published</option>
                    <option value="draft" @selected(old('status', $job->status) === 'draft')>Draft</option>
                </select>
                @error('status')<p class="field-error">{{ $message }}</p>@enderror
            </div>

            <button type="submit" class="bg-forest px-5 py-3.5 text-sm font-semibold text-white transition hover:bg-forest-dark">Save changes</button>
        </form>
    </section>
</div>
@endsection
