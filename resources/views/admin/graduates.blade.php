@extends('layouts.app')

@section('title', 'Graduate master list')

@section('content')
<div class="mx-auto max-w-7xl px-5 py-10 sm:px-8 lg:py-14">
    <!-- <div class="flex flex-col justify-between gap-6 border-b border-line pb-8 sm:flex-row sm:items-end"><div><p class="eyebrow">Administration</p><h1 class="font-display mt-3 text-4xl sm:text-5xl">Graduate master list</h1></div><div class="flex flex-wrap items-end gap-6"><a href="{{ route('admin.announcements.index') }}" class="mb-1 text-sm font-semibold text-forest underline underline-offset-4">Announcements</a><a href="{{ route('admin.surveys.index') }}" class="mb-1 text-sm font-semibold text-forest underline underline-offset-4">Surveys</a><div class="flex flex-wrap gap-8"><div><p class="text-xs uppercase tracking-wider text-muted">Graduates</p><p class="mt-1 font-display text-3xl">{{ number_format($graduateCount) }}</p></div><div><p class="text-xs uppercase tracking-wider text-muted">Accounts claimed</p><p class="mt-1 font-display text-3xl">{{ number_format($claimedCount) }}</p></div><div><p class="text-xs uppercase tracking-wider text-muted">Profiles complete</p><p class="mt-1 font-display text-3xl">{{ number_format($profileCount) }}</p></div></div></div></div> -->
    <div class="mt-8 grid gap-6 lg:grid-cols-[0.8fr_1.2fr]">
        <section class="border border-line bg-white p-5 sm:p-6">
            <h2 class="font-display text-2xl">Add a graduate</h2>
            <form method="POST" action="{{ route('admin.graduates.store') }}" class="mt-5 grid gap-4">@csrf
                <div class="field"><label for="student_number">Student number</label><input id="student_number" name="student_number" value="{{ old('student_number') }}" required>@error('student_number')<p class="field-error">{{ $message }}</p>@enderror</div>
                <div class="field"><label for="first_name">First name</label><input id="first_name" name="first_name" value="{{ old('first_name') }}" required>@error('first_name')<p class="field-error">{{ $message }}</p>@enderror</div>
                <div class="field"><label for="middle_name">Middle name</label><input id="middle_name" name="middle_name" value="{{ old('middle_name') }}">@error('middle_name')<p class="field-error">{{ $message }}</p>@enderror</div>
                <div class="field"><label for="last_name">Last name</label><input id="last_name" name="last_name" value="{{ old('last_name') }}" required>@error('last_name')<p class="field-error">{{ $message }}</p>@enderror</div>
                <div class="field"><label for="program_code">Program code</label><input id="program_code" name="program_code" value="{{ old('program_code') }}">@error('program_code')<p class="field-error">{{ $message }}</p>@enderror</div>
                <div class="field"><label for="batch_year">Batch year</label><input id="batch_year" type="number" min="1900" max="2100" name="batch_year" value="{{ old('batch_year') }}">@error('batch_year')<p class="field-error">{{ $message }}</p>@enderror</div>
                <div class="field"><label for="latin_honor">Latin honor</label><select id="latin_honor" name="latin_honor" required>@foreach (\App\Models\Graduate::LATIN_HONORS as $honor)<option value="{{ $honor }}" @selected(old('latin_honor', 'none') === $honor)>{{ ucwords(str_replace('_', ' ', $honor)) }}</option>@endforeach</select>@error('latin_honor')<p class="field-error">{{ $message }}</p>@enderror</div>
                <button class="mt-1 bg-forest px-4 py-3 text-sm font-semibold text-white hover:bg-forest-dark" type="submit">Add to master list</button>
            </form>
            <div class="my-6 border-t border-line"></div>
            <h2 class="font-display text-2xl">Import a CSV</h2>
            <form method="POST" action="{{ route('admin.graduates.import') }}" enctype="multipart/form-data" class="mt-5 grid gap-4">@csrf
                <div class="field"><label for="file">CSV file</label><input id="file" type="file" name="file" accept=".csv,text/csv" required>@error('file')<p class="field-error">{{ $message }}</p>@enderror</div>
                <p class="text-xs leading-5 text-muted">Headers: student_number, first_name, last_name, middle_name, program_code, batch_year, latin_honor</p>
                <button class="border border-forest px-4 py-3 text-sm font-semibold text-forest transition hover:bg-mint" type="submit">Import graduate records</button>
            </form>
        </section>
        <section class="min-w-0">
            <div class="flex flex-col gap-4 pb-4 sm:flex-row sm:items-center sm:justify-between"><h2 class="font-display text-2xl">Roster <span class="font-sans text-sm text-muted">({{ $graduates->total() }})</span></h2><form method="GET" action="{{ route('admin.graduates.index') }}" class="flex gap-2"><label class="sr-only" for="search">Search graduates</label><input id="search" name="search" value="{{ $search }}" placeholder="Name or student number" class="w-full min-w-0 border border-line bg-white px-3 py-2 text-sm outline-none focus:border-forest sm:w-64"><button class="border border-ink px-3 py-2 text-sm font-semibold hover:bg-ink hover:text-white" type="submit">Search</button></form></div>
            <div class="overflow-x-auto rounded-md border border-line bg-white"><table class="data-table w-full min-w-[620px] border-collapse text-left text-sm">
                <thead class="bg-paper text-xs uppercase tracking-wider text-muted"><tr><th class="px-4 py-3 font-semibold">Graduate</th><th class="px-4 py-3 font-semibold">Student number</th><th class="px-4 py-3 font-semibold">Program / batch</th><th class="px-4 py-3 font-semibold">Latin honor</th><th class="px-4 py-3 font-semibold">Alumni profile</th></tr></thead>
                <tbody class="divide-y divide-line">@forelse ($graduates as $graduate)@php($profile = $graduate->user?->profile)<tr><td class="px-4 py-4"><div class="flex items-center justify-between gap-2"><a href="{{ route('admin.graduates.show', $graduate) }}" class="font-medium text-forest underline underline-offset-4">{{ $graduate->fullName() }}</a><a href="{{ route('admin.graduates.edit', $graduate) }}" class="text-xs font-semibold text-forest underline underline-offset-4">Edit</a></div></td><td class="px-4 py-4 text-muted">{{ $graduate->student_number }}</td><td class="px-4 py-4 text-muted">{{ $graduate->program_code ?: 'Not listed' }}{{ $graduate->batch_year ? ' / '.$graduate->batch_year : '' }}</td><td class="px-4 py-4 text-muted">{{ ucwords(str_replace('_', ' ', $graduate->latin_honor)) }}</td><td class="px-4 py-4">@if ($profile?->completed_at)<span class="font-medium text-forest">{{ $profile->job_title }} · {{ $profile->employer }}</span><span class="mt-1 block text-xs text-muted">{{ $profile->industry }} · {{ $profile->city }}, {{ $profile->country }}</span>@elseif ($graduate->user)<span class="text-xs font-medium text-terracotta">Account claimed · profile incomplete</span>@else<span class="text-xs text-muted">Not registered</span>@endif</td></tr>@empty<tr><td colspan="5" class="px-4 py-12 text-center text-sm text-muted">No graduate records found.</td></tr>@endforelse</tbody>
            </table></div>
            <div class="mt-5">{{ $graduates->links() }}</div>
        </section>
    </div>
</div>
@endsection
