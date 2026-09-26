<div>
    <!-- Because you are alive, everything is possible. - Thich Nhat Hanh -->
@extends('layouts.app')

@section('title', 'Survey manager')

@section('content')
<div class="mx-auto max-w-7xl px-5 py-10 sm:px-8 lg:py-14">
    <div class="flex flex-col justify-between gap-6 border-b border-line pb-8 sm:flex-row sm:items-end">
        <div><p class="eyebrow">Administration</p><h1 class="font-display mt-3 text-4xl sm:text-5xl">Survey manager</h1></div>
    </div>

    <div class="mt-8 grid items-start gap-8 lg:grid-cols-[0.9fr_1.1fr]">
        <section class="border border-line bg-white p-5 sm:p-6">
            <p class="eyebrow">New survey</p>
            <h2 class="font-display mt-2 text-2xl">Create a survey</h2>
            <form method="POST" action="{{ route('admin.surveys.store') }}" class="mt-5 grid gap-5">
                @csrf
                <div class="field"><label for="title">Survey title</label><input id="title" name="title" maxlength="180" value="{{ old('title') }}" required>@error('title')<p class="field-error">{{ $message }}</p>@enderror</div>
                <div class="field"><label for="description">Description <span class="font-normal text-muted">Optional</span></label><textarea id="description" name="description" rows="3" maxlength="5000">{{ old('description') }}</textarea>@error('description')<p class="field-error">{{ $message }}</p>@enderror</div>
                <div class="field"><label for="questions_text">Questions</label><textarea id="questions_text" name="questions_text" rows="5" placeholder="One question per line" required>{{ old('questions_text') }}</textarea><p class="text-xs text-muted">Each line becomes a required long-answer question. Maximum 20.</p>@error('questions')<p class="field-error">{{ $message }}</p>@enderror</div>
                <fieldset class="grid gap-3">
                    <legend class="text-sm font-semibold">Who should respond?</legend>
                    <p class="text-xs leading-5 text-muted">Choose no programs or years to include all graduates. If you choose both, respondents must match both.</p>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div><p class="mb-2 text-xs font-semibold uppercase tracking-wider text-muted">Programs</p><div class="max-h-44 overflow-y-auto border border-line p-3">
                            @forelse ($programs as $program)
                                <label class="flex items-start gap-2 py-1.5 text-sm"><input class="mt-1 accent-[#245b49]" type="checkbox" name="target_programs[]" value="{{ $program }}" @checked(in_array($program, old('target_programs', [])))><span>{{ $program }}</span></label>
                            @empty
                                <p class="text-xs text-muted">No programs in the master list.</p>
                            @endforelse
                        </div>@error('target_programs.*')<p class="field-error">{{ $message }}</p>@enderror</div>
                        <div><p class="mb-2 text-xs font-semibold uppercase tracking-wider text-muted">Graduation years</p><div class="max-h-44 overflow-y-auto border border-line p-3">
                            @forelse ($graduationYears as $year)
                                <label class="flex items-start gap-2 py-1.5 text-sm"><input class="mt-1 accent-[#245b49]" type="checkbox" name="target_graduation_years[]" value="{{ $year }}" @checked(in_array($year, old('target_graduation_years', [])))><span>{{ $year }}</span></label>
                            @empty
                                <p class="text-xs text-muted">No graduation years in the master list.</p>
                            @endforelse
                        </div>@error('target_graduation_years.*')<p class="field-error">{{ $message }}</p>@enderror</div>
                    </div>
                </fieldset>
                <div class="field"><label for="closes_at">Closes at <span class="font-normal text-muted">Optional</span></label><input id="closes_at" type="datetime-local" name="closes_at" value="{{ old('closes_at') }}">@error('closes_at')<p class="field-error">{{ $message }}</p>@enderror</div>
                <div class="field"><label for="status">Status</label><select id="status" name="status"><option value="active" @selected(old('status', 'active') === 'active')>Open for responses</option><option value="draft" @selected(old('status') === 'draft')>Save as draft</option></select>@error('status')<p class="field-error">{{ $message }}</p>@enderror</div>
                <button class="flex items-center justify-between bg-forest px-5 py-3.5 text-sm font-semibold text-white hover:bg-forest-dark" type="submit"><span>Create survey</span><span aria-hidden="true">&#8599;</span></button>
            </form>
        </section>

        <section class="min-w-0">
            <div class="mb-4 flex items-end justify-between gap-4"><h2 class="font-display text-2xl">Surveys</h2><span class="text-xs uppercase tracking-wider text-muted">{{ $surveys->total() }} total</span></div>
            <div class="grid gap-3">
                @forelse ($surveys as $survey)
                    <article class="border border-line bg-white p-5 sm:p-6">
                        <div class="flex flex-wrap items-start justify-between gap-3"><div><span class="text-xs font-semibold uppercase tracking-wider {{ $survey->is_active ? 'text-forest' : 'text-terracotta' }}">{{ $survey->is_active ? 'Open' : 'Draft / closed' }}</span><h3 class="mt-2 font-display text-2xl">{{ $survey->title }}</h3></div><span class="text-xs text-muted">{{ $survey->responses_count }} responses</span></div>
                        @if ($survey->description)<p class="mt-3 text-sm leading-6 text-muted">{{ $survey->description }}</p>@endif
                        <div class="mt-4 flex flex-wrap gap-2 text-xs text-muted">
                            @if ($survey->target_programs === [] && $survey->target_graduation_years === [])<span class="border border-line px-2 py-1">All graduates</span>@endif
                            @foreach ($survey->target_programs as $program)<span class="border border-line px-2 py-1">{{ $program }}</span>@endforeach
                            @foreach ($survey->target_graduation_years as $year)<span class="border border-line px-2 py-1">Class of {{ $year }}</span>@endforeach
                        </div>
                        <p class="mt-3 text-xs text-muted">{{ count($survey->questions) }} questions{{ $survey->closes_at ? ' · Closes '.$survey->closes_at->format('M j, Y g:i A') : '' }}</p>
                        <div class="mt-5 flex flex-wrap gap-4 border-t border-line pt-4">
                            <a class="text-sm font-semibold text-forest underline underline-offset-4" href="{{ route('admin.surveys.edit', $survey) }}">Edit</a>
                            <a class="text-sm font-semibold text-forest underline underline-offset-4" href="{{ route('admin.surveys.show', $survey) }}">View responses ({{ $survey->responses_count }})</a>
                            <form method="POST" action="{{ route('admin.surveys.update', $survey) }}">@csrf @method('PATCH')<input type="hidden" name="status" value="{{ $survey->is_active ? 'draft' : 'active' }}"><button class="text-sm font-semibold text-forest underline underline-offset-4" type="submit">{{ $survey->is_active ? 'Close survey' : 'Reopen survey' }}</button></form>
                            <form method="POST" action="{{ route('admin.surveys.destroy', $survey) }}">@csrf @method('DELETE')<button class="text-sm font-medium text-terracotta underline underline-offset-4" type="submit">Delete</button></form>
                        </div>
                    </article>
                @empty
                    <p class="border border-dashed border-line px-5 py-12 text-center text-sm text-muted">No surveys created yet.</p>
                @endforelse
            </div>
            <div class="mt-5">{{ $surveys->links() }}</div>
        </section>
    </div>
</div>
@endsection
