@extends('layouts.app')

@section('title', 'Create survey')

@section('content')
<div class="mx-auto max-w-5xl px-5 py-7 sm:px-8">
    <div class="mb-5 flex items-center justify-between gap-4 border-b border-line pb-5">
        <div>
            <p class="eyebrow">Administration</p>
            <h1 class="mt-1 text-xl font-semibold">Create survey</h1>
        </div>
        <a href="{{ route('admin.surveys.index') }}" class="text-xs font-medium text-forest underline underline-offset-4">Cancel</a>
    </div>

    <section class="rounded-md border border-line bg-surface p-4 sm:p-6">
        <form method="POST" action="{{ route('admin.surveys.store') }}" class="grid gap-5">
            @csrf
            <div class="field"><label for="title">Survey title</label><input id="title" name="title" maxlength="180" value="{{ old('title') }}" required>@error('title')<p class="field-error">{{ $message }}</p>@enderror</div>
            <div class="field"><label for="description">Description <span class="font-normal text-muted">Optional</span></label><textarea id="description" name="description" rows="3" maxlength="5000">{{ old('description') }}</textarea>@error('description')<p class="field-error">{{ $message }}</p>@enderror</div>

            @php($questionEntries = old('questions', [['prompt' => '', 'section_label' => '', 'type' => 'short_text', 'required' => true, 'options' => []]]))
            @include('admin.partials.survey-question-builder', ['questionEntries' => $questionEntries])

            <fieldset class="grid gap-3">
                <legend class="text-sm font-semibold">Who should respond?</legend>
                <p class="text-xs leading-5 text-muted">Choose no program codes or batch years to include all graduates. If both are selected, respondents must match both.</p>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <p class="mb-2 text-xs font-semibold uppercase tracking-wider text-muted">Programs</p>
                        <div class="max-h-44 overflow-y-auto rounded-md border border-line bg-surface-raised p-3">
                            @forelse ($programs as $program)
                                <label class="flex items-start gap-2 py-1.5 text-sm"><input class="mt-1" type="checkbox" name="target_programs[]" value="{{ $program }}" @checked(in_array($program, old('target_programs', [])))><span>{{ $program }}</span></label>
                            @empty
                                <p class="text-xs text-muted">No programs in the master list.</p>
                            @endforelse
                        </div>
                        @error('target_programs.*')<p class="field-error">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <p class="mb-2 text-xs font-semibold uppercase tracking-wider text-muted">Batch years</p>
                        <div class="max-h-44 overflow-y-auto rounded-md border border-line bg-surface-raised p-3">
                            @forelse ($graduationYears as $year)
                                <label class="flex items-start gap-2 py-1.5 text-sm"><input class="mt-1" type="checkbox" name="target_graduation_years[]" value="{{ $year }}" @checked(in_array($year, old('target_graduation_years', [])))><span>{{ $year }}</span></label>
                            @empty
                                <p class="text-xs text-muted">No graduation years in the master list.</p>
                            @endforelse
                        </div>
                        @error('target_graduation_years.*')<p class="field-error">{{ $message }}</p>@enderror
                    </div>
                </div>
            </fieldset>

            <div class="grid gap-4 sm:grid-cols-2">
                <div class="field"><label for="closes_at">Closes at</label><input id="closes_at" type="datetime-local" name="closes_at" value="{{ old('closes_at') }}">@error('closes_at')<p class="field-error">{{ $message }}</p>@enderror</div>
                <div class="field"><label for="status">Status</label><select id="status" name="status"><option value="active" @selected(old('status', 'active') === 'active')>Open for responses</option><option value="draft" @selected(old('status') === 'draft')>Save as draft</option></select>@error('status')<p class="field-error">{{ $message }}</p>@enderror</div>
            </div>
            <button class="bg-forest px-5 py-3.5 text-sm font-semibold text-white transition hover:bg-forest-dark" type="submit">Create survey</button>
        </form>
    </section>
</div>
@endsection