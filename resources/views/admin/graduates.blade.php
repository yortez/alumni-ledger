@extends('layouts.app')

@section('title', 'Graduate master list')

@section('content')
<div class="mx-auto max-w-7xl px-5 py-7 sm:px-8">
    <header class="mb-5 flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-semibold">Alumni Records</h1>
            <p class="mt-1 text-xs text-muted">{{ $graduates->total() }} {{ $graduates->total() === 1 ? 'alumnus' : 'alumni' }} found</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('admin.graduates.export', request()->query()) }}" class="inline-flex min-h-9 items-center gap-2 rounded-md border border-line bg-surface px-3 py-2 text-xs font-medium text-muted transition hover:border-ink hover:text-ink"><svg aria-hidden="true" class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3v12m0 0 4-4m-4 4-4-4M4 17v4h16v-4" /></svg>Export CSV</a>
            <details class="relative">
                <summary class="inline-flex min-h-9 cursor-pointer list-none items-center gap-2 rounded-md bg-forest px-3 py-2 text-xs font-semibold text-white transition hover:bg-forest-dark"><span aria-hidden="true">+</span>Add Alumni</summary>
                <div class="absolute right-0 z-20 mt-2 max-h-[75vh] w-[min(92vw,34rem)] overflow-y-auto rounded-md border border-line bg-surface p-5 shadow-xl">
                    <h2 class="text-sm font-semibold">Add alumni record</h2>
                    <form method="POST" action="{{ route('admin.graduates.store') }}" class="mt-4 grid gap-4 sm:grid-cols-2">@csrf
                        <div class="field"><label for="student_number">Student number</label><input id="student_number" name="student_number" value="{{ old('student_number') }}" required>@error('student_number')<p class="field-error">{{ $message }}</p>@enderror</div>
                        <div class="field"><label for="first_name">First name</label><input id="first_name" name="first_name" value="{{ old('first_name') }}" required>@error('first_name')<p class="field-error">{{ $message }}</p>@enderror</div>
                        <div class="field"><label for="middle_name">Middle name</label><input id="middle_name" name="middle_name" value="{{ old('middle_name') }}">@error('middle_name')<p class="field-error">{{ $message }}</p>@enderror</div>
                        <div class="field"><label for="last_name">Last name</label><input id="last_name" name="last_name" value="{{ old('last_name') }}" required>@error('last_name')<p class="field-error">{{ $message }}</p>@enderror</div>
                        <div class="field"><label for="program_code">Program code</label><input id="program_code" name="program_code" value="{{ old('program_code') }}">@error('program_code')<p class="field-error">{{ $message }}</p>@enderror</div>
                        <div class="field"><label for="batch_year">Batch year</label><input id="batch_year" type="number" min="1900" max="2100" name="batch_year" value="{{ old('batch_year') }}">@error('batch_year')<p class="field-error">{{ $message }}</p>@enderror</div>
                        <div class="field sm:col-span-2"><label for="latin_honor">Latin honor</label><select id="latin_honor" name="latin_honor" required>@foreach (\App\Models\Graduate::LATIN_HONORS as $honor)<option value="{{ $honor }}" @selected(old('latin_honor', 'none') === $honor)>{{ ucwords(str_replace('_', ' ', $honor)) }}</option>@endforeach</select>@error('latin_honor')<p class="field-error">{{ $message }}</p>@enderror</div>
                        <button class="bg-forest px-4 py-2.5 text-sm font-semibold text-white hover:bg-forest-dark sm:col-span-2" type="submit">Add to records</button>
                    </form>
                    <details class="mt-4 border-t border-line pt-4">
                        <summary class="cursor-pointer text-sm font-medium text-forest">Import records from CSV</summary>
                        <form method="POST" action="{{ route('admin.graduates.import') }}" enctype="multipart/form-data" class="mt-4 grid gap-3">@csrf
                            <div class="field"><label for="file">CSV file</label><input id="file" type="file" name="file" accept=".csv,text/csv" required>@error('file')<p class="field-error">{{ $message }}</p>@enderror</div>
                            <p class="text-xs leading-5 text-muted">Headers: student_number, first_name, last_name, middle_name, program_code, batch_year, latin_honor</p>
                            <a href="{{ route('admin.graduates.import-template') }}" class="text-xs font-medium text-forest underline underline-offset-4">Download sample CSV template</a>
                            <button class="border border-forest px-4 py-2.5 text-sm font-semibold text-forest transition hover:bg-mint" type="submit">Import CSV</button>
                        </form>
                    </details>
                </div>
            </details>
        </div>
    </header>

    <form method="GET" action="{{ route('admin.graduates.index') }}" class="mb-4 grid gap-3 rounded-md border border-line bg-surface p-4 sm:grid-cols-2 lg:grid-cols-4">
        <label class="relative block"><span class="sr-only">Search alumni</span><svg aria-hidden="true" class="absolute left-3 top-1/2 size-3.5 -translate-y-1/2 text-muted" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="11" cy="11" r="7" /><path d="m16 16 4 4" /></svg><input type="search" name="search" value="{{ $filters['search'] }}" placeholder="Search name, email, student number" class="w-full rounded-md border border-line bg-surface-raised py-2 pl-9 pr-3 text-xs text-ink placeholder:text-muted focus:border-forest focus:outline-none"></label>
        <label><span class="sr-only">Program</span><select name="program" class="w-full rounded-md border border-line bg-surface-raised px-3 py-2 text-xs text-ink focus:border-forest focus:outline-none"><option value="">All Programs</option>@foreach ($programs as $program)<option value="{{ $program }}" @selected($filters['program'] === $program)>{{ $program }}</option>@endforeach</select></label>
        <label><span class="sr-only">Batch</span><select name="batch" class="w-full rounded-md border border-line bg-surface-raised px-3 py-2 text-xs text-ink focus:border-forest focus:outline-none"><option value="">All Batches</option>@foreach ($batches as $batch)<option value="{{ $batch }}" @selected($filters['batch'] === (string) $batch)>{{ $batch }}</option>@endforeach</select></label>
        <label><span class="sr-only">Account status</span><select name="status" class="w-full rounded-md border border-line bg-surface-raised px-3 py-2 text-xs text-ink focus:border-forest focus:outline-none"><option value="">All Status</option><option value="verified" @selected($filters['status'] === 'verified')>Verified</option><option value="unregistered" @selected($filters['status'] === 'unregistered')>Not registered</option></select></label>
        <div class="flex flex-wrap gap-2 sm:col-span-2 lg:col-span-4">
            <button type="submit" class="inline-flex min-h-8 items-center gap-2 rounded-md bg-forest px-4 py-2 text-xs font-semibold text-white transition hover:bg-forest-dark"><svg aria-hidden="true" class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 5h16l-6 7v6l-4 2v-8L4 5z" /></svg>Filter</button>
            <a href="{{ route('admin.graduates.index') }}" class="inline-flex min-h-8 items-center rounded-md border border-line bg-surface-raised px-4 py-2 text-xs font-medium text-muted transition hover:text-ink">Reset</a>
        </div>
    </form>

    <div class="overflow-x-auto rounded-md border border-line bg-surface">
        <table class="data-table w-full min-w-[900px] border-collapse text-left text-xs">
            <thead class="bg-surface-raised text-[10px] uppercase tracking-wide text-muted"><tr><th class="px-4 py-3 font-semibold">Alumni</th><th class="px-4 py-3 font-semibold">Program</th><th class="px-4 py-3 font-semibold">Batch</th><th class="px-4 py-3 font-semibold">Contact</th><th class="px-4 py-3 font-semibold">Employment</th><th class="px-4 py-3 font-semibold">Verification</th><th class="px-4 py-3 text-right font-semibold">Actions</th></tr></thead>
            <tbody class="divide-y divide-line">
                @forelse ($graduates as $graduate)
                    @php($profile = $graduate->user?->profile)
                    @php($employment = $profile?->employments->first())
                    @php($firstName = $profile?->first_name ?: $graduate->first_name)
                    @php($middleName = $profile?->middle_name ?: $graduate->middle_name)
                    @php($lastName = $profile?->last_name ?: $graduate->last_name)
                    @php($displayName = strtoupper(trim($lastName.', '.$firstName.($middleName ? ' '.substr($middleName, 0, 1).'.' : '').($profile?->suffix ? ' '.$profile->suffix : ''))))
                    @php($initials = strtoupper(substr($firstName, 0, 1).substr($lastName, 0, 1)))
                    <tr>
                        <td class="px-4 py-3"><div class="flex min-w-0 items-center gap-3"><span class="grid size-8 shrink-0 place-items-center rounded-full bg-forest text-[10px] font-bold text-white">{{ $initials ?: 'A' }}</span><div class="min-w-0"><a href="{{ route('admin.graduates.show', $graduate) }}" class="block truncate font-semibold text-ink hover:text-forest">{{ $displayName ?: strtoupper($graduate->student_number) }}</a><span class="mt-1 block text-[10px] text-muted">{{ $graduate->student_number }}</span></div></div></td>
                        <td class="px-4 py-3"><span class="rounded-full bg-forest/15 px-2.5 py-1 text-[10px] font-semibold text-forest">{{ $graduate->program_code ?: '—' }}</span></td>
                        <td class="px-4 py-3 font-medium text-ink">{{ $graduate->batch_year ?: '—' }}</td>
                        <td class="px-4 py-3"><span class="block max-w-48 truncate text-muted">{{ $graduate->user?->email ?? '—' }}</span>@if ($profile?->phone)<span class="mt-1 block text-[10px] text-muted">{{ $profile->phone }}</span>@endif</td>
                        <td class="px-4 py-3">@if ($employment?->is_current)<span class="rounded-full bg-mint px-2.5 py-1 text-[10px] font-semibold text-forest">Employed</span><span class="mt-1 block max-w-40 truncate text-[10px] text-muted">{{ $employment->job_title }} · {{ $employment->employer }}</span>@else<span class="text-muted">—</span>@endif</td>
                        <td class="px-4 py-3">@if ($graduate->user)<span class="rounded-full bg-mint px-2.5 py-1 text-[10px] font-semibold text-forest">Verified</span>@else<span class="text-muted">Not registered</span>@endif</td>
                        <td class="px-4 py-3"><div class="flex justify-end gap-1.5"><a href="{{ route('admin.graduates.show', $graduate) }}" title="View alumni profile" aria-label="View alumni profile" class="grid size-8 place-items-center rounded-md border border-line text-muted transition hover:bg-surface-raised hover:text-ink"><svg aria-hidden="true" class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7z" /><circle cx="12" cy="12" r="3" /></svg></a><a href="{{ route('admin.graduates.edit', $graduate) }}" title="Edit alumni record" aria-label="Edit alumni record" class="grid size-8 place-items-center rounded-md border border-line text-muted transition hover:bg-surface-raised hover:text-ink"><svg aria-hidden="true" class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="m16 4 4 4L9 19l-5 1 1-5L16 4zM14 6l4 4" /></svg></a></div></td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-4 py-12 text-center text-sm text-muted">No alumni records match these filters.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $graduates->links() }}</div>
    </div>
</div>
@endsection
