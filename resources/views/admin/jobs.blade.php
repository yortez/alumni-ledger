@extends('layouts.app')

@section('title', 'Job opportunities')

@section('content')
<div class="mx-auto max-w-7xl px-4 py-6 sm:px-7">
    <header class="mb-5 flex flex-wrap items-center justify-between gap-4">
        <div><h1 class="text-xl font-semibold">Job Opportunities</h1><p class="mt-1 text-xs text-muted">{{ $jobCount }} jobs · {{ $publishedCount }} published</p></div>
        <details class="relative">
            <summary class="inline-flex min-h-9 cursor-pointer list-none items-center gap-2 rounded-md bg-forest px-4 py-2 text-xs font-semibold text-white transition hover:bg-forest-dark"><span aria-hidden="true">+</span>Post Job</summary>
            <div class="absolute right-0 z-20 mt-2 max-h-[82vh] w-[min(92vw,40rem)] overflow-y-auto rounded-md border border-line bg-surface p-5 shadow-xl">
                <h2 class="text-sm font-semibold">Post a new role</h2>
                <form method="POST" action="{{ route('admin.jobs.store') }}" class="mt-4 grid gap-4 sm:grid-cols-2">@csrf
                    <div class="field"><label for="title">Job title</label><input id="title" name="title" maxlength="180" value="{{ old('title') }}" required>@error('title')<p class="field-error">{{ $message }}</p>@enderror</div>
                    <div class="field"><label for="company">Company</label><input id="company" name="company" maxlength="180" value="{{ old('company') }}" required>@error('company')<p class="field-error">{{ $message }}</p>@enderror</div>
                    <div class="field"><label for="location">Location</label><input id="location" name="location" maxlength="180" value="{{ old('location') }}" required>@error('location')<p class="field-error">{{ $message }}</p>@enderror</div>
                    <div class="field"><label for="employment_type">Employment type</label><input id="employment_type" name="employment_type" maxlength="80" value="{{ old('employment_type', 'Full-time') }}" required>@error('employment_type')<p class="field-error">{{ $message }}</p>@enderror</div>
                    <div class="field sm:col-span-2"><label for="description">Description</label><textarea id="description" name="description" rows="4" maxlength="10000" required>{{ old('description') }}</textarea>@error('description')<p class="field-error">{{ $message }}</p>@enderror</div>
                    <div class="field sm:col-span-2"><label for="requirements">Requirements <span class="font-normal text-muted">Optional</span></label><textarea id="requirements" name="requirements" rows="3" maxlength="10000">{{ old('requirements') }}</textarea>@error('requirements')<p class="field-error">{{ $message }}</p>@enderror</div>
                    <div class="field"><label for="application_deadline">Application deadline</label><input id="application_deadline" name="application_deadline" type="date" min="{{ today()->toDateString() }}" value="{{ old('application_deadline') }}">@error('application_deadline')<p class="field-error">{{ $message }}</p>@enderror</div>
                    <div class="field"><label for="status">Status</label><select id="status" name="status" required><option value="published" @selected(old('status', 'published') === 'published')>Published</option><option value="draft" @selected(old('status') === 'draft')>Draft</option></select>@error('status')<p class="field-error">{{ $message }}</p>@enderror</div>
                    <button type="submit" class="bg-forest px-4 py-2.5 text-sm font-semibold text-white hover:bg-forest-dark sm:col-span-2">Create job</button>
                </form>
            </div>
        </details>
    </header>

    <form method="GET" action="{{ route('admin.jobs.index') }}" class="mb-4 flex flex-wrap items-center gap-2 rounded-md border border-line bg-surface p-3 sm:p-4">
        <label class="relative block w-full sm:max-w-72"><span class="sr-only">Search jobs</span><svg aria-hidden="true" class="absolute left-3 top-1/2 size-3.5 -translate-y-1/2 text-muted" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="11" cy="11" r="7" /><path d="m16 16 4 4" /></svg><input type="search" name="search" value="{{ $filters['search'] }}" placeholder="Search title, company, location..." class="w-full rounded-md border border-line bg-surface-raised py-2 pl-9 pr-3 text-xs text-ink placeholder:text-muted focus:border-forest focus:outline-none"></label>
        <label><span class="sr-only">Job status</span><select name="status" class="min-w-36 rounded-md border border-line bg-surface-raised px-3 py-2 text-xs text-ink focus:border-forest focus:outline-none"><option value="">All Status</option><option value="published" @selected($filters['status'] === 'published')>Published</option><option value="draft" @selected($filters['status'] === 'draft')>Draft</option></select></label>
        <button type="submit" class="inline-flex min-h-9 items-center gap-2 rounded-md bg-forest px-4 py-2 text-xs font-semibold text-white transition hover:bg-forest-dark">Filter</button><a href="{{ route('admin.jobs.index') }}" class="inline-flex min-h-9 items-center rounded-md border border-line bg-surface-raised px-4 py-2 text-xs font-medium text-muted transition hover:text-ink">Reset</a>
    </form>

    <section class="grid gap-3">
        @forelse ($jobs as $job)
            <article class="overflow-hidden rounded-md border border-line bg-surface">
                <div class="flex flex-col justify-between gap-3 p-4 sm:flex-row sm:items-start sm:px-5">
                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-2 text-[10px]"><span class="rounded-full px-2.5 py-1 font-semibold {{ $job->status === 'published' ? 'bg-mint text-forest' : 'bg-surface-raised text-muted' }}">{{ ucfirst($job->status) }}</span><span class="text-muted">{{ $job->applications_count }} applications</span><span class="text-muted">{{ $job->employment_type }}</span></div>
                        <h2 class="mt-2 text-sm font-semibold">{{ $job->title }}</h2>
                        <p class="mt-1 text-xs text-muted">{{ $job->company }} · {{ $job->location }}</p>
                        @if ($job->application_deadline)<p class="mt-2 text-[10px] text-muted">Apply by {{ $job->application_deadline->format('M j, Y') }}</p>@endif
                    </div>
                    <div class="flex shrink-0 flex-wrap items-center gap-1.5">
                        <a href="{{ route('admin.jobs.applications.index', $job) }}" class="rounded-md border border-line px-2.5 py-2 text-[10px] font-medium text-muted hover:text-ink">Applications</a>
                        <a href="{{ route('admin.jobs.edit', $job) }}" title="Edit job" aria-label="Edit job" class="grid size-8 place-items-center rounded-md border border-line text-muted transition hover:bg-surface-raised hover:text-ink"><svg aria-hidden="true" class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="m16 4 4 4L9 19l-5 1 1-5L16 4zM14 6l4 4" /></svg></a>
                        <form method="POST" action="{{ route('admin.jobs.update', $job) }}">@csrf @method('PATCH')<input type="hidden" name="status" value="{{ $job->status === 'published' ? 'draft' : 'published' }}"><button type="submit" class="rounded-md border border-line px-2.5 py-2 text-[10px] font-medium text-muted hover:text-ink">{{ $job->status === 'published' ? 'Move to draft' : 'Publish' }}</button></form>
                        <form method="POST" action="{{ route('admin.jobs.destroy', $job) }}">@csrf @method('DELETE')<button type="submit" title="Delete job" aria-label="Delete job" class="grid size-8 place-items-center rounded-md bg-danger text-white hover:bg-danger/80"><svg aria-hidden="true" class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18M8 6V4h8v2m3 0-1 14H6L5 6m4 4v6m6-6v6" /></svg></button></form>
                    </div>
                </div>
            </article>
        @empty
            <p class="rounded-md border border-dashed border-line px-5 py-12 text-center text-sm text-muted">No job postings match these filters.</p>
        @endforelse
    </section>
    <div class="mt-4">{{ $jobs->links() }}</div>
</div>
@endsection