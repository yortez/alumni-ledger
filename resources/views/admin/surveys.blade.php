<div>
    <!-- Because you are alive, everything is possible. - Thich Nhat Hanh -->
@extends('layouts.app')

@section('title', 'Survey manager')

@section('content')
<div class="mx-auto max-w-7xl px-4 py-6 sm:px-7">
    <header class="mb-5 flex flex-wrap items-center justify-between gap-4">
        <div><h1 class="text-xl font-semibold">Survey manager</h1><p class="mt-1 text-xs text-muted">{{ $surveyCount }} surveys</p></div>
        <details class="relative">
            <summary class="inline-flex min-h-9 cursor-pointer list-none items-center gap-2 rounded-md bg-forest px-4 py-2 text-xs font-semibold text-white transition hover:bg-forest-dark"><span aria-hidden="true">+</span>Create Survey</summary>
            <div class="absolute right-0 z-20 mt-2 max-h-[82vh] w-[min(92vw,52rem)] overflow-y-auto rounded-md border border-line bg-surface shadow-xl">
                <h2 class="border-b border-line px-4 py-3 text-sm font-semibold">New survey</h2>
                <form method="POST" action="{{ route('admin.surveys.store') }}" class="grid gap-4 p-4 sm:p-5">
                    @csrf
                    <div class="field"><label for="title">Survey title</label><input id="title" name="title" maxlength="180" value="{{ old('title') }}" required>@error('title')<p class="field-error">{{ $message }}</p>@enderror</div>
                    <div class="field"><label for="description">Description <span class="font-normal text-muted">Optional</span></label><textarea id="description" name="description" rows="3" maxlength="5000">{{ old('description') }}</textarea>@error('description')<p class="field-error">{{ $message }}</p>@enderror</div>
                    @php($questionEntries = old('questions', [['prompt' => '', 'section_label' => '', 'type' => 'short_text', 'required' => true, 'options' => []]]))
                    @include('admin.partials.survey-question-builder', ['questionEntries' => $questionEntries])
                    <fieldset class="grid gap-3"><legend class="text-sm font-semibold">Who should respond?</legend><p class="text-xs leading-5 text-muted">Choose no program codes or batch years to include all graduates. If both are selected, respondents must match both.</p><div class="grid gap-4 sm:grid-cols-2"><div><p class="mb-2 text-xs font-semibold uppercase tracking-wider text-muted">Programs</p><div class="max-h-36 overflow-y-auto rounded-md border border-line bg-surface-raised p-3">@forelse ($programs as $program)<label class="flex items-start gap-2 py-1.5 text-xs"><input class="mt-1" type="checkbox" name="target_programs[]" value="{{ $program }}" @checked(in_array($program, old('target_programs', [])))><span>{{ $program }}</span></label>@empty<p class="text-xs text-muted">No programs in the master list.</p>@endforelse</div>@error('target_programs.*')<p class="field-error">{{ $message }}</p>@enderror</div><div><p class="mb-2 text-xs font-semibold uppercase tracking-wider text-muted">Batch years</p><div class="max-h-36 overflow-y-auto rounded-md border border-line bg-surface-raised p-3">@forelse ($graduationYears as $year)<label class="flex items-start gap-2 py-1.5 text-xs"><input class="mt-1" type="checkbox" name="target_graduation_years[]" value="{{ $year }}" @checked(in_array($year, old('target_graduation_years', [])))><span>{{ $year }}</span></label>@empty<p class="text-xs text-muted">No graduation years in the master list.</p>@endforelse</div>@error('target_graduation_years.*')<p class="field-error">{{ $message }}</p>@enderror</div></div></fieldset>
                    <div class="grid gap-4 sm:grid-cols-2"><div class="field"><label for="closes_at">Closes at</label><input id="closes_at" type="datetime-local" name="closes_at" value="{{ old('closes_at') }}">@error('closes_at')<p class="field-error">{{ $message }}</p>@enderror</div><div class="field"><label for="status">Status</label><select id="status" name="status"><option value="active" @selected(old('status', 'active') === 'active')>Open for responses</option><option value="draft" @selected(old('status') === 'draft')>Save as draft</option></select>@error('status')<p class="field-error">{{ $message }}</p>@enderror</div></div>
                    <button class="bg-forest px-4 py-2.5 text-sm font-semibold text-white hover:bg-forest-dark" type="submit">Create survey</button>
                </form>
            </div>
        </details>
    </header>

    <form method="GET" action="{{ route('admin.surveys.index') }}" class="mb-4 flex flex-wrap items-center gap-2 rounded-md border border-line bg-surface p-3 sm:p-4">
        <label class="relative block w-full sm:max-w-72"><span class="sr-only">Search surveys</span><svg aria-hidden="true" class="absolute left-3 top-1/2 size-3.5 -translate-y-1/2 text-muted" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="11" cy="11" r="7" /><path d="m16 16 4 4" /></svg><input type="search" name="search" value="{{ $filters['search'] }}" placeholder="Search surveys..." class="w-full rounded-md border border-line bg-surface-raised py-2 pl-9 pr-3 text-xs text-ink placeholder:text-muted focus:border-forest focus:outline-none"></label>
        <label><span class="sr-only">Survey status</span><select name="status" class="min-w-36 rounded-md border border-line bg-surface-raised px-3 py-2 text-xs text-ink focus:border-forest focus:outline-none"><option value="">All Status</option><option value="open" @selected($filters['status'] === 'open')>Open</option><option value="draft" @selected($filters['status'] === 'draft')>Draft</option><option value="closed" @selected($filters['status'] === 'closed')>Closed</option></select></label>
        <button type="submit" class="inline-flex min-h-9 items-center gap-2 rounded-md bg-forest px-4 py-2 text-xs font-semibold text-white transition hover:bg-forest-dark">Filter</button><a href="{{ route('admin.surveys.index') }}" class="inline-flex min-h-9 items-center rounded-md border border-line bg-surface-raised px-4 py-2 text-xs font-medium text-muted transition hover:text-ink">Reset</a>
    </form>

    <section class="grid gap-3">
        @forelse ($surveys as $survey)
            @php($isClosed = $survey->is_active && $survey->closes_at?->isPast())
            <article class="overflow-hidden rounded-md border border-line bg-surface">
                <div class="flex flex-col justify-between gap-3 p-4 sm:flex-row sm:items-start sm:px-5">
                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-2 text-[10px]"><span class="rounded-full px-2.5 py-1 font-semibold {{ $survey->is_active && ! $isClosed ? 'bg-mint text-forest' : 'bg-surface-raised text-muted' }}">{{ $isClosed ? 'Closed' : ($survey->is_active ? 'Open' : 'Draft') }}</span><span class="text-muted">{{ $survey->responses_count }} responses</span></div>
                        <h2 class="mt-2 text-sm font-semibold">{{ $survey->title }}</h2>
                        @if ($survey->description)<p class="mt-2 text-xs leading-5 text-muted">{{ $survey->description }}</p>@endif
                        <div class="mt-2 flex flex-wrap gap-1.5 text-[10px] text-muted">@if ($survey->target_programs === [] && $survey->target_graduation_years === [])<span class="rounded-full bg-surface-raised px-2 py-1">All graduates</span>@endif @foreach ($survey->target_programs as $program)<span class="rounded-full bg-surface-raised px-2 py-1">{{ $program }}</span>@endforeach @foreach ($survey->target_graduation_years as $year)<span class="rounded-full bg-surface-raised px-2 py-1">Class of {{ $year }}</span>@endforeach</div>
                        <p class="mt-2 text-[10px] text-muted">{{ count($survey->questions) }} questions{{ $survey->closes_at ? ' · Closes '.$survey->closes_at->format('M j, Y g:i A') : '' }}</p>
                    </div>
                    <div class="flex shrink-0 flex-wrap items-center gap-1.5">
                        <a href="{{ route('admin.surveys.show', $survey) }}" class="rounded-md border border-line px-2.5 py-2 text-[10px] font-medium text-muted hover:text-ink">Responses ({{ $survey->responses_count }})</a>
                        <a href="{{ route('admin.surveys.edit', $survey) }}" title="Edit survey" aria-label="Edit survey" class="grid size-8 place-items-center rounded-md border border-line text-muted transition hover:bg-surface-raised hover:text-ink"><svg aria-hidden="true" class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="m16 4 4 4L9 19l-5 1 1-5L16 4zM14 6l4 4" /></svg></a>
                        <form method="POST" action="{{ route('admin.surveys.update', $survey) }}">@csrf @method('PATCH')<input type="hidden" name="status" value="{{ $survey->is_active ? 'draft' : 'active' }}"><button type="submit" class="rounded-md border border-line px-2.5 py-2 text-[10px] font-medium text-muted hover:text-ink">{{ $survey->is_active ? 'Close' : 'Reopen' }}</button></form>
                        <form method="POST" action="{{ route('admin.surveys.destroy', $survey) }}">@csrf @method('DELETE')<button type="submit" title="Delete survey" aria-label="Delete survey" class="grid size-8 place-items-center rounded-md bg-danger text-white hover:bg-danger/80"><svg aria-hidden="true" class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18M8 6V4h8v2m3 0-1 14H6L5 6m4 4v6m6-6v6" /></svg></button></form>
                    </div>
                </div>
            </article>
        @empty
            <p class="rounded-md border border-dashed border-line px-5 py-12 text-center text-sm text-muted">No surveys match these filters.</p>
        @endforelse
    </section>
    <div class="mt-4">{{ $surveys->links() }}</div>
</div>
@endsection
