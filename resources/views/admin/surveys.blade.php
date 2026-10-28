<div>
    <!-- Because you are alive, everything is possible. - Thich Nhat Hanh -->
@extends('layouts.app')

@section('title', 'Survey manager')

@section('content')
<div class="mx-auto max-w-7xl px-4 py-6 sm:px-7">
    <header class="mb-5 flex flex-wrap items-center justify-between gap-4">
        <div><h1 class="text-xl font-semibold">Survey manager</h1><p class="mt-1 text-xs text-muted">{{ $surveyCount }} surveys</p></div>
        <a href="{{ route('admin.surveys.create') }}" class="inline-flex min-h-9 items-center gap-2 rounded-md bg-forest px-4 py-2 text-xs font-semibold text-white transition hover:bg-forest-dark"><span aria-hidden="true">+</span>Create Survey</a>
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
                        <h2 class="mt-2 text-sm font-semibold"><button type="button" data-survey-edit-open data-survey-id="{{ $survey->id }}" data-update-url="{{ route('admin.surveys.update', $survey) }}" data-delete-url="{{ route('admin.surveys.destroy', $survey) }}" aria-haspopup="dialog" aria-controls="survey-edit-dialog" class="text-left underline decoration-transparent underline-offset-4 transition hover:text-forest hover:decoration-current focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-forest">{{ $survey->title }}</button></h2>
                        @if ($survey->description)<p class="mt-2 text-xs leading-5 text-muted">{{ $survey->description }}</p>@endif
                        <div class="mt-2 flex flex-wrap gap-1.5 text-[10px] text-muted">@if ($survey->target_programs === [] && $survey->target_graduation_years === [])<span class="rounded-full bg-surface-raised px-2 py-1">All graduates</span>@endif @foreach ($survey->target_programs as $program)<span class="rounded-full bg-surface-raised px-2 py-1">{{ $program }}</span>@endforeach @foreach ($survey->target_graduation_years as $year)<span class="rounded-full bg-surface-raised px-2 py-1">Class of {{ $year }}</span>@endforeach</div>
                        <p class="mt-2 text-[10px] text-muted">{{ count($survey->questions) }} questions{{ $survey->closes_at ? ' · Closes '.$survey->closes_at->format('M j, Y g:i A') : '' }}</p>
                    </div>
                    <div class="flex shrink-0 flex-wrap items-center gap-1.5">
                        <a href="{{ route('admin.surveys.show', $survey) }}" class="rounded-md border border-line px-2.5 py-2 text-[10px] font-medium text-muted hover:text-ink">Responses ({{ $survey->responses_count }})</a>
                    </div>
                </div>
            </article>
                        <template data-survey-edit-data="{{ $survey->id }}">{{ json_encode(['title' => $survey->title, 'description' => $survey->description, 'questions' => $survey->normalizedQuestions(), 'target_programs' => $survey->target_programs ?? [], 'target_graduation_years' => $survey->target_graduation_years ?? [], 'closes_at' => $survey->closes_at?->format('Y-m-d\\TH:i'), 'status' => $survey->is_active ? 'active' : 'draft']) }}</template>
        @empty
            <p class="rounded-md border border-dashed border-line px-5 py-12 text-center text-sm text-muted">No surveys match these filters.</p>
        @endforelse
    </section>
    <div class="mt-4">{{ $surveys->links() }}</div>
    <dialog id="survey-edit-dialog" data-reopen-for="{{ old('survey_id') }}" aria-labelledby="survey-edit-title" class="m-auto max-h-[calc(100vh-2rem)] w-[calc(100%-2rem)] max-w-4xl overflow-y-auto rounded-md border border-line bg-surface p-0 text-ink shadow-2xl backdrop:bg-black/45">
        <div class="flex items-center justify-between gap-4 border-b border-line px-5 py-4">
            <div><p class="eyebrow">Survey manager</p><h2 id="survey-edit-title" class="mt-1 text-lg font-semibold">Edit survey</h2></div>
            <button type="button" data-survey-edit-close aria-label="Close edit dialog" title="Close" class="grid size-9 shrink-0 place-items-center rounded-md border border-line text-muted transition hover:bg-paper hover:text-ink"><svg aria-hidden="true" class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="m18 6-12 12M6 6l12 12" /></svg></button>
        </div>
        @if ($errors->any())<p class="mx-5 mt-4 rounded-md border border-red-300 bg-red-50 px-3 py-2 text-xs text-red-800" role="alert">{{ $errors->first() }}</p>@endif
        <form method="POST" action="{{ route('admin.surveys.index') }}" data-survey-edit-form class="grid gap-5 px-5 py-5">@csrf @method('PATCH')
            <input type="hidden" name="survey_id" value="{{ old('survey_id') }}">
            <label class="grid gap-1.5 text-xs font-medium text-muted">Survey title<input name="title" value="{{ old('title') }}" maxlength="180" required class="min-w-0 rounded-md border border-line bg-surface-raised px-3 py-2.5 text-xs text-ink"></label>
            <label class="grid gap-1.5 text-xs font-medium text-muted">Description<textarea name="description" rows="3" maxlength="5000" class="min-w-0 rounded-md border border-line bg-surface-raised px-3 py-2.5 text-xs text-ink">{{ old('description') }}</textarea></label>
            @include('admin.partials.survey-question-builder', ['questionEntries' => old('questions', [])])
            <fieldset class="grid gap-3"><legend class="text-sm font-semibold">Who should respond?</legend><div class="grid gap-4 sm:grid-cols-2">
                <div><p class="mb-2 text-xs font-semibold uppercase tracking-wider text-muted">Programs</p><div class="max-h-44 overflow-y-auto rounded-md border border-line p-3">@foreach ($programs as $program)<label class="flex items-start gap-2 py-1.5 text-sm"><input class="mt-1 accent-[#245b49]" type="checkbox" name="target_programs[]" value="{{ $program }}" @checked(in_array($program, old('target_programs', [])))><span>{{ $program }}</span></label>@endforeach</div></div>
                <div><p class="mb-2 text-xs font-semibold uppercase tracking-wider text-muted">Batch years</p><div class="max-h-44 overflow-y-auto rounded-md border border-line p-3">@foreach ($graduationYears as $year)<label class="flex items-start gap-2 py-1.5 text-sm"><input class="mt-1 accent-[#245b49]" type="checkbox" name="target_graduation_years[]" value="{{ $year }}" @checked(in_array((int) $year, old('target_graduation_years', [])))><span>{{ $year }}</span></label>@endforeach</div></div>
            </div></fieldset>
            <div class="grid gap-4 sm:grid-cols-2"><label class="grid gap-1.5 text-xs font-medium text-muted">Closes at<input type="datetime-local" name="closes_at" value="{{ old('closes_at') }}" class="min-w-0 rounded-md border border-line bg-surface-raised px-3 py-2.5 text-xs text-ink"></label><label class="grid gap-1.5 text-xs font-medium text-muted">Status<select name="status" class="min-w-0 rounded-md border border-line bg-surface-raised px-3 py-2.5 text-xs text-ink"><option value="active" @selected(old('status') === 'active')>Open for responses</option><option value="draft" @selected(old('status') === 'draft')>Save as draft</option></select></label></div>
            <div class="flex justify-end gap-2 border-t border-line pt-4"><button type="button" data-survey-edit-close class="inline-flex min-h-9 items-center justify-center rounded-md border border-line px-3 py-2 text-xs font-medium text-muted transition hover:text-ink">Cancel</button><button type="submit" class="inline-flex min-h-9 items-center justify-center rounded-md bg-forest px-4 py-2 text-xs font-semibold text-white transition hover:bg-forest-dark">Save changes</button></div>
        </form>
        <form method="POST" action="{{ route('admin.surveys.index') }}" data-survey-delete-form onsubmit="return confirm('Delete this survey?')" class="border-t border-line px-5 py-3">@csrf @method('DELETE')<button type="submit" class="inline-flex min-h-9 items-center justify-center rounded-md border border-red-300 px-3 py-2 text-xs font-medium text-red-700 transition hover:bg-red-50">Delete survey</button></form>
    </dialog>
</div>
@endsection
