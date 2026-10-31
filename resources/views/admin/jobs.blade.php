@extends('layouts.app')

@section('title', 'Job opportunities')

@section('content')
<div class="mx-auto w-full max-w-[1440px] px-5 pb-16 pt-8 sm:px-8 xl:px-20">
    <header class="mb-5 flex flex-wrap items-center justify-between gap-4">
        <div><h1 class="text-display font-semibold">Job Opportunities</h1><p class="mt-1 text-meta text-ink-tertiary">{{ $jobCount }} jobs · {{ $publishedCount }} published</p></div>
        <a href="{{ route('admin.jobs.create') }}" class="inline-flex min-h-9 items-center gap-2 rounded-md bg-forest px-4 py-2 text-xs font-semibold text-white transition hover:bg-forest-dark"><span aria-hidden="true">+</span>Post Job</a>
    </header>

    <form method="GET" action="{{ route('admin.jobs.index') }}" class="panel mb-4 flex flex-wrap items-center gap-2 p-3 sm:p-4">
        <label class="relative block w-full sm:max-w-72"><span class="sr-only">Search jobs</span><svg aria-hidden="true" class="absolute left-3 top-1/2 size-3.5 -translate-y-1/2 text-muted" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="11" cy="11" r="7" /><path d="m16 16 4 4" /></svg><input type="search" name="search" value="{{ $filters['search'] }}" placeholder="Search title, company, location..." class="w-full rounded-md border border-line bg-surface-raised py-2 pl-9 pr-3 text-xs text-ink placeholder:text-muted focus:border-forest focus:outline-none"></label>
        <label><span class="sr-only">Job status</span><select name="status" class="min-w-36 rounded-md border border-line bg-surface-raised px-3 py-2 text-xs text-ink focus:border-forest focus:outline-none"><option value="">All Status</option><option value="published" @selected($filters['status'] === 'published')>Published</option><option value="draft" @selected($filters['status'] === 'draft')>Draft</option></select></label>
        <button type="submit" class="inline-flex min-h-9 items-center gap-2 rounded-md bg-forest px-4 py-2 text-xs font-semibold text-white transition hover:bg-forest-dark">Filter</button><a href="{{ route('admin.jobs.index') }}" class="inline-flex min-h-9 items-center rounded-md border border-line bg-surface-raised px-4 py-2 text-xs font-medium text-muted transition hover:text-ink">Reset</a>
    </form>

    <section class="grid gap-3">
        @forelse ($jobs as $job)
            <article class="panel overflow-hidden">
                <div class="flex flex-col justify-between gap-3 p-4 sm:flex-row sm:items-start sm:px-5">
                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-2 text-[10px]"><span class="rounded-full px-2.5 py-1 font-semibold {{ $job->status === 'published' ? 'bg-mint text-forest' : 'bg-surface-raised text-muted' }}">{{ ucfirst($job->status) }}</span><span class="text-muted">{{ $job->applications_count }} applications</span><span class="text-muted">{{ $job->employment_type }}</span></div>
                        <h2 class="mt-2 text-sm font-semibold"><button type="button" data-job-edit-open data-job-id="{{ $job->id }}" data-job-title="{{ $job->title }}" data-job-company="{{ $job->company }}" data-job-location="{{ $job->location }}" data-job-employment-type="{{ $job->employment_type }}" data-job-description="{{ $job->description }}" data-job-requirements="{{ $job->requirements }}" data-job-deadline="{{ $job->application_deadline?->toDateString() }}" data-job-status="{{ $job->status }}" data-update-url="{{ route('admin.jobs.update', $job) }}" data-delete-url="{{ route('admin.jobs.destroy', $job) }}" aria-haspopup="dialog" aria-controls="job-edit-dialog" class="text-left text-ink underline decoration-transparent underline-offset-4 transition hover:text-forest hover:decoration-current focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-forest">{{ $job->title }}</button></h2>
                        <p class="mt-1 text-xs text-muted">{{ $job->company }} · {{ $job->location }}</p>
                        @if ($job->application_deadline)<p class="mt-2 text-[10px] text-muted">Apply by {{ $job->application_deadline->format('M j, Y') }}</p>@endif
                    </div>
                    <div class="flex shrink-0 flex-wrap items-center gap-1.5">
                        <a href="{{ route('admin.jobs.applications.index', $job) }}" class="rounded-md border border-line px-2.5 py-2 text-[10px] font-medium text-muted hover:text-ink">Applications</a>
                    </div>
                </div>
            </article>
        @empty
            <p class="rounded-md border border-dashed border-line px-5 py-12 text-center text-sm text-muted">No job postings match these filters.</p>
        @endforelse
    </section>
    <div class="mt-4">{{ $jobs->links() }}</div>
    <dialog id="job-edit-dialog" data-reopen-for="{{ old('job_id') }}" aria-labelledby="job-edit-title" class="m-auto max-h-[calc(100vh-2rem)] w-[calc(100%-2rem)] max-w-3xl overflow-y-auto rounded-card border border-hairline bg-surface p-0 text-ink shadow-2xl backdrop:bg-black/45">
        <div class="flex items-center justify-between gap-4 border-b border-line px-5 py-4">
            <div><p class="eyebrow">Job opportunities</p><h2 id="job-edit-title" class="mt-1 text-lg font-semibold">Edit job posting</h2></div>
            <button type="button" data-job-edit-close aria-label="Close edit dialog" title="Close" class="grid size-9 shrink-0 place-items-center rounded-md border border-line text-muted transition hover:bg-paper hover:text-ink"><svg aria-hidden="true" class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="m18 6-12 12M6 6l12 12" /></svg></button>
        </div>
        @if ($errors->any())<p class="mx-5 mt-4 rounded-md border border-red-300 bg-red-50 px-3 py-2 text-xs text-red-800" role="alert">{{ $errors->first() }}</p>@endif
        <form method="POST" action="{{ route('admin.jobs.index') }}" data-job-edit-form class="grid gap-4 px-5 py-5 sm:grid-cols-2">@csrf @method('PATCH')
            <input type="hidden" name="job_id" value="{{ old('job_id') }}">
            <label class="grid gap-1.5 text-xs font-medium text-muted">Job title<input name="title" value="{{ old('title') }}" maxlength="180" required class="min-w-0 rounded-md border border-line bg-surface-raised px-3 py-2.5 text-xs text-ink"></label>
            <label class="grid gap-1.5 text-xs font-medium text-muted">Company<input name="company" value="{{ old('company') }}" maxlength="180" required class="min-w-0 rounded-md border border-line bg-surface-raised px-3 py-2.5 text-xs text-ink"></label>
            <label class="grid gap-1.5 text-xs font-medium text-muted">Location<input name="location" value="{{ old('location') }}" maxlength="180" required class="min-w-0 rounded-md border border-line bg-surface-raised px-3 py-2.5 text-xs text-ink"></label>
            <label class="grid gap-1.5 text-xs font-medium text-muted">Employment type<input name="employment_type" value="{{ old('employment_type') }}" maxlength="80" required class="min-w-0 rounded-md border border-line bg-surface-raised px-3 py-2.5 text-xs text-ink"></label>
            <label class="grid gap-1.5 text-xs font-medium text-muted sm:col-span-2">Description<textarea name="description" rows="5" maxlength="10000" required class="min-w-0 rounded-md border border-line bg-surface-raised px-3 py-2.5 text-xs text-ink">{{ old('description') }}</textarea></label>
            <label class="grid gap-1.5 text-xs font-medium text-muted sm:col-span-2">Requirements <span class="font-normal">Optional</span><textarea name="requirements" rows="4" maxlength="10000" class="min-w-0 rounded-md border border-line bg-surface-raised px-3 py-2.5 text-xs text-ink">{{ old('requirements') }}</textarea></label>
            <label class="grid gap-1.5 text-xs font-medium text-muted">Application deadline <span class="font-normal">Optional</span><input type="date" name="application_deadline" min="{{ today()->toDateString() }}" value="{{ old('application_deadline') }}" class="min-w-0 rounded-md border border-line bg-surface-raised px-3 py-2.5 text-xs text-ink"></label>
            <label class="grid gap-1.5 text-xs font-medium text-muted">Status<select name="status" required class="min-w-0 rounded-md border border-line bg-surface-raised px-3 py-2.5 text-xs text-ink"><option value="published" @selected(old('status') === 'published')>Published</option><option value="draft" @selected(old('status') === 'draft')>Draft</option></select></label>
            <div class="flex justify-end gap-2 border-t border-line pt-4 sm:col-span-2"><button type="button" data-job-edit-close class="inline-flex min-h-9 items-center justify-center rounded-md border border-line px-3 py-2 text-xs font-medium text-muted transition hover:text-ink">Cancel</button><button type="submit" class="inline-flex min-h-9 items-center justify-center rounded-md bg-forest px-4 py-2 text-xs font-semibold text-white transition hover:bg-forest-dark">Save changes</button></div>
        </form>
        <form method="POST" action="{{ route('admin.jobs.index') }}" data-job-delete-form onsubmit="return confirm('Delete this job posting?')" class="flex items-center justify-between gap-4 border-t border-line px-5 py-3">@csrf @method('DELETE')<button type="submit" class="inline-flex min-h-9 items-center justify-center rounded-md border border-red-300 px-3 py-2 text-xs font-medium text-red-700 transition hover:bg-red-50">Delete job</button><span class="text-[10px] text-muted">Deleting a job also removes its applications.</span></form>
    </dialog>
</div>
@endsection
