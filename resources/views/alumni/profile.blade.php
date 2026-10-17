@extends('layouts.app')

@section('title', 'Alumni profile')

@section('content')
<div class="mx-auto max-w-7xl px-5 py-8 sm:px-8 lg:py-10">
    <div class="mb-6 border-b border-line pb-5">
        <p class="eyebrow">Your details</p>
        <h1 class="font-display mt-2 text-3xl font-semibold sm:text-4xl">Edit profile</h1>
        <p class="mt-2 text-sm text-muted">{{ auth()->user()->name }} · {{ auth()->user()->student_number }}</p>
    </div>

    <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="grid items-start gap-5 lg:grid-cols-[minmax(0,1fr)_15rem]">
        @csrf
        @method('PUT')

        <div class="grid gap-5">
            <section class="border border-line bg-white">
                <div class="border-b border-line px-4 py-3 sm:px-5">
                    <h2 class="text-sm font-semibold">Personal Information</h2>
                </div>
                <div class="grid gap-x-5 gap-y-4 p-4 sm:grid-cols-2 sm:p-5 xl:grid-cols-12">
                    <div class="field xl:col-span-4"><label for="first_name">First name <span class="text-danger">*</span></label><input id="first_name" name="first_name" value="{{ old('first_name', $profile?->first_name ?? $graduate?->first_name) }}" autocomplete="given-name" required>@error('first_name')<p class="field-error">{{ $message }}</p>@enderror</div>
                    <div class="field xl:col-span-4"><label for="middle_name">Middle name</label><input id="middle_name" name="middle_name" value="{{ old('middle_name', $profile?->middle_name ?? $graduate?->middle_name) }}" autocomplete="additional-name">@error('middle_name')<p class="field-error">{{ $message }}</p>@enderror</div>
                    <div class="field xl:col-span-4"><label for="last_name">Last name <span class="text-danger">*</span></label><input id="last_name" name="last_name" value="{{ old('last_name', $profile?->last_name ?? $graduate?->last_name) }}" autocomplete="family-name" required>@error('last_name')<p class="field-error">{{ $message }}</p>@enderror</div>
                    <div class="field xl:col-span-3"><label for="suffix">Suffix</label><select id="suffix" name="suffix"><option value="">None</option>@foreach (['Jr.', 'Sr.', 'II', 'III', 'IV'] as $suffix)<option value="{{ $suffix }}" @selected(old('suffix', $profile?->suffix) === $suffix)>{{ $suffix }}</option>@endforeach</select>@error('suffix')<p class="field-error">{{ $message }}</p>@enderror</div>
                    <div class="field xl:col-span-3"><label for="birthdate">Birthdate</label><input id="birthdate" type="date" name="birthdate" value="{{ old('birthdate', $profile?->birthdate?->toDateString()) }}" autocomplete="bday">@error('birthdate')<p class="field-error">{{ $message }}</p>@enderror</div>
                    <div class="field xl:col-span-3"><label for="sex">Sex</label><select id="sex" name="sex"><option value="">Select</option>@foreach (['male' => 'Male', 'female' => 'Female', 'other' => 'Other', 'prefer_not_to_say' => 'Prefer not to say'] as $value => $label)<option value="{{ $value }}" @selected(old('sex', $profile?->sex) === $value)>{{ $label }}</option>@endforeach</select>@error('sex')<p class="field-error">{{ $message }}</p>@enderror</div>
                    <div class="field xl:col-span-3"><label for="civil_status">Civil status</label><select id="civil_status" name="civil_status"><option value="">Select</option>@foreach (['single' => 'Single', 'married' => 'Married', 'widowed' => 'Widowed', 'divorced' => 'Divorced', 'separated' => 'Separated'] as $value => $label)<option value="{{ $value }}" @selected(old('civil_status', $profile?->civil_status) === $value)>{{ $label }}</option>@endforeach</select>@error('civil_status')<p class="field-error">{{ $message }}</p>@enderror</div>
                    <div class="field sm:col-span-1 xl:col-span-6"><label for="phone">Contact number <span class="text-danger">*</span></label><input id="phone" name="phone" value="{{ old('phone', $profile?->phone) }}" autocomplete="tel" placeholder="09XXXXXXXXX" required>@error('phone')<p class="field-error">{{ $message }}</p>@enderror</div>
                    <div class="field sm:col-span-1 xl:col-span-6"><label for="email">Email address <span class="text-danger">*</span></label><input id="email" type="email" name="email" value="{{ old('email', auth()->user()->email) }}" autocomplete="email" required>@error('email')<p class="field-error">{{ $message }}</p>@enderror</div>
                    <div class="field sm:col-span-2 xl:col-span-12"><label for="current_address">Current address</label><textarea id="current_address" name="current_address" rows="3" maxlength="2000" autocomplete="street-address">{{ old('current_address', $profile?->current_address ?? trim(($profile?->city ?? '').', '.($profile?->country ?? ''), ', ')) }}</textarea>@error('current_address')<p class="field-error">{{ $message }}</p>@enderror</div>
                    <div class="field sm:col-span-2 xl:col-span-12"><label for="permanent_address">Permanent address</label><textarea id="permanent_address" name="permanent_address" rows="3" maxlength="2000">{{ old('permanent_address', $profile?->permanent_address) }}</textarea>@error('permanent_address')<p class="field-error">{{ $message }}</p>@enderror</div>
                </div>
            </section>

            <section class="border border-line bg-white">
                <div class="border-b border-line px-4 py-3 sm:px-5">
                    <h2 class="text-sm font-semibold">Academic Information</h2>
                </div>
                <div class="grid gap-x-5 gap-y-4 p-4 sm:grid-cols-2 sm:p-5">
                    <div class="field"><label for="student_number">Student number</label><input id="student_number" value="{{ auth()->user()->student_number }}" readonly></div>
                    <div class="field"><label for="program_code">Program</label><input id="program_code" value="{{ $graduate?->program_code ?? 'Not available' }}" readonly></div>
                    <div class="field"><label for="batch_year">Batch year</label><input id="batch_year" value="{{ $graduate?->batch_year ?? 'Not available' }}" readonly></div>
                    <div class="field"><label for="latin_honor">Latin honor</label><input id="latin_honor" value="{{ $graduate ? ucwords(str_replace('_', ' ', $graduate->latin_honor)) : 'Not available' }}" readonly></div>
                </div>
            </section>

        </div>

        <aside class="grid gap-5 lg:sticky lg:top-4">
            <section class="border border-line bg-white">
                <h2 class="border-b border-line px-4 py-3 text-sm font-semibold">Profile Photo</h2>
                <div class="grid gap-3 p-4">
                    @if ($profile?->profile_photo_path)
                        <img src="{{ route('profile.files', 'photo') }}" alt="Profile photo" class="mx-auto size-16 rounded-full object-cover">
                    @else
                        <div class="mx-auto grid size-16 place-items-center rounded-full bg-forest text-lg font-semibold text-white">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</div>
                    @endif
                    <div class="field"><label for="profile_photo" class="sr-only">Upload profile photo</label><input id="profile_photo" type="file" name="profile_photo" accept=".jpg,.jpeg,.png,.gif,image/jpeg,image/png,image/gif">@error('profile_photo')<p class="field-error">{{ $message }}</p>@enderror</div>
                    <p class="text-[0.65rem] text-muted">JPG, PNG, GIF. Max 5MB</p>
                </div>
            </section>
            <section class="border border-line bg-white">
                <h2 class="border-b border-line px-4 py-3 text-sm font-semibold">Documents</h2>
                <div class="grid gap-3 p-4">
                    <label for="diploma_document" class="text-xs uppercase tracking-wide text-muted">Diploma / TOR</label>
                    @if ($profile?->diploma_document_path)
                        <a href="{{ route('profile.files', 'diploma') }}" class="text-xs font-medium text-forest underline underline-offset-4">Download uploaded document</a>
                    @endif
                    <div class="field"><input id="diploma_document" type="file" name="diploma_document" accept=".pdf,.doc,.docx,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document">@error('diploma_document')<p class="field-error">{{ $message }}</p>@enderror</div>
                    <p class="text-[0.65rem] text-muted">PDF, DOC, DOCX. Max 5MB</p>
                </div>
            </section>
            <section class="grid gap-3 border border-line bg-white p-4">
                <button type="submit" class="w-full bg-forest px-4 py-3 text-sm font-semibold text-white transition hover:bg-forest-dark">Save profile</button>
                <a href="{{ route('dashboard') }}" class="w-full border border-line px-4 py-2.5 text-center text-sm font-medium text-muted transition hover:bg-paper hover:text-ink">Cancel</a>
            </section>
        </aside>
    </form>

    <section id="employment-history" class="mt-10 border-t border-line pt-8">
        <div class="mb-6 flex flex-col justify-between gap-3 sm:flex-row sm:items-end">
            <div><p class="eyebrow">Career timeline</p><h2 class="font-display mt-2 text-3xl">Employment history</h2></div>
            <p class="text-sm text-muted">Add current and previous roles to your profile.</p>
        </div>
        @if (session('employment_status'))
            <p class="mb-5 border-l-2 border-forest bg-mint px-4 py-3 text-sm text-forest">{{ session('employment_status') }}</p>
        @endif

        <div class="grid gap-4">
            @foreach ($employmentRecords as $index => $employment)
                @php($employmentFormId = 'employment-form-'.$employment->id)
                <article class="overflow-hidden rounded-md border border-line bg-surface">
                    <header class="flex flex-wrap items-center justify-between gap-3 border-b border-line px-4 py-3 sm:px-5">
                        <div class="flex min-w-0 items-center gap-3">
                            <span class="grid size-6 shrink-0 place-items-center rounded-full bg-forest text-xs font-semibold text-white">{{ $index + 1 }}</span>
                            <h3 class="truncate text-sm font-semibold">{{ $employment->job_title ?: 'Employment Entry' }}</h3>
                            @if ($employment->is_current)<span class="rounded-full bg-mint px-2 py-1 text-[10px] font-semibold text-forest">Current</span>@endif
                        </div>
                        <div class="flex items-center gap-3">
                            <input type="hidden" name="is_current" value="0" form="{{ $employmentFormId }}">
                            <label class="inline-flex items-center gap-1.5 text-xs text-muted"><input type="checkbox" name="is_current" value="1" form="{{ $employmentFormId }}" @checked($employment->is_current)> Current</label>
                            <form method="POST" action="{{ route('profile.employments.destroy', $employment) }}">@csrf @method('DELETE')<button type="submit" title="Remove employment entry" aria-label="Remove employment entry" class="grid size-7 place-items-center rounded bg-danger/15 text-danger transition hover:bg-danger/25"><svg aria-hidden="true" class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18M8 6V4h8v2m3 0-1 14H6L5 6m4 4v6m6-6v6" /></svg></button></form>
                        </div>
                    </header>
                    <form id="{{ $employmentFormId }}" method="POST" action="{{ route('profile.employments.update', $employment) }}" class="p-4 sm:p-5">
                        @csrf @method('PATCH')
                        <div class="grid gap-x-3 gap-y-4 sm:grid-cols-2 lg:grid-cols-6">
                            <div class="field lg:col-span-3"><label for="employment_status_{{ $employment->id }}">Employment status <span class="text-danger">*</span></label><select id="employment_status_{{ $employment->id }}" name="employment_status" required><option value="">Select status</option>@foreach (\App\Models\Employment::EMPLOYMENT_STATUSES as $value => $label)<option value="{{ $value }}" @selected(old('employment_status', $employment->employment_status) === $value)>{{ $label }}</option>@endforeach</select>@error('employment_status')<p class="field-error">{{ $message }}</p>@enderror</div>
                            <div class="field lg:col-span-3"><label for="sector_{{ $employment->id }}">Sector</label><select id="sector_{{ $employment->id }}" name="sector"><option value="">Select sector</option>@foreach (\App\Models\Employment::SECTORS as $value => $label)<option value="{{ $value }}" @selected(old('sector', $employment->sector) === $value)>{{ $label }}</option>@endforeach</select>@error('sector')<p class="field-error">{{ $message }}</p>@enderror</div>
                            <div class="field sm:col-span-1 lg:col-span-3"><label for="employer_{{ $employment->id }}">Company / employer name</label><input id="employer_{{ $employment->id }}" name="employer" value="{{ old('employer', $employment->employer) }}" placeholder="Company name" required>@error('employer')<p class="field-error">{{ $message }}</p>@enderror</div>
                            <div class="field sm:col-span-1 lg:col-span-3"><label for="job_title_{{ $employment->id }}">Job title / position</label><input id="job_title_{{ $employment->id }}" name="job_title" value="{{ old('job_title', $employment->job_title) }}" placeholder="e.g. Software Developer" required>@error('job_title')<p class="field-error">{{ $message }}</p>@enderror</div>
                            <div class="field lg:col-span-2"><label for="industry_{{ $employment->id }}">Industry</label><input id="industry_{{ $employment->id }}" name="industry" value="{{ old('industry', $employment->industry) }}" placeholder="e.g. Information Technology">@error('industry')<p class="field-error">{{ $message }}</p>@enderror</div>
                            <div class="field lg:col-span-2"><label for="job_level_{{ $employment->id }}">Job level</label><select id="job_level_{{ $employment->id }}" name="job_level"><option value="">Select level</option>@foreach (\App\Models\Employment::JOB_LEVELS as $value => $label)<option value="{{ $value }}" @selected(old('job_level', $employment->job_level) === $value)>{{ $label }}</option>@endforeach</select>@error('job_level')<p class="field-error">{{ $message }}</p>@enderror</div>
                            <div class="field lg:col-span-2"><label for="monthly_income_{{ $employment->id }}">Monthly income</label><select id="monthly_income_{{ $employment->id }}" name="monthly_income"><option value="">Select income</option>@foreach (\App\Models\Employment::MONTHLY_INCOME_RANGES as $value => $label)<option value="{{ $value }}" @selected(old('monthly_income', $employment->monthly_income ?? 'prefer_not_to_say') === $value)>{{ $label }}</option>@endforeach</select>@error('monthly_income')<p class="field-error">{{ $message }}</p>@enderror</div>
                            <div class="field lg:col-span-2"><label for="location_{{ $employment->id }}">Work location</label><input id="location_{{ $employment->id }}" name="location" value="{{ old('location', $employment->location) }}" placeholder="City, Province">@error('location')<p class="field-error">{{ $message }}</p>@enderror</div>
                            <div class="field lg:col-span-2"><label for="country_{{ $employment->id }}">Country</label><input id="country_{{ $employment->id }}" name="country" value="{{ old('country', $employment->country ?? 'Philippines') }}">@error('country')<p class="field-error">{{ $message }}</p>@enderror</div>
                            <div class="field lg:col-span-2"><label for="months_to_employment_{{ $employment->id }}">Months to employment</label><input id="months_to_employment_{{ $employment->id }}" type="number" min="0" max="600" name="months_to_employment" value="{{ old('months_to_employment', $employment->months_to_employment) }}" placeholder="From graduation">@error('months_to_employment')<p class="field-error">{{ $message }}</p>@enderror</div>
                            <div class="field lg:col-span-2"><label for="course_relevance_{{ $employment->id }}">Course relevance</label><select id="course_relevance_{{ $employment->id }}" name="course_relevance"><option value="">Select relevance</option>@foreach (\App\Models\Employment::COURSE_RELEVANCE_OPTIONS as $value => $label)<option value="{{ $value }}" @selected(old('course_relevance', $employment->course_relevance ?? 'not_specified') === $value)>{{ $label }}</option>@endforeach</select>@error('course_relevance')<p class="field-error">{{ $message }}</p>@enderror</div>
                            <div class="field lg:col-span-2"><label for="start_date_{{ $employment->id }}">Date started</label><input id="start_date_{{ $employment->id }}" type="date" name="start_date" value="{{ old('start_date', $employment->start_date?->toDateString()) }}" required>@error('start_date')<p class="field-error">{{ $message }}</p>@enderror</div>
                            <div class="field lg:col-span-2"><label for="end_date_{{ $employment->id }}">Date ended</label><input id="end_date_{{ $employment->id }}" type="date" name="end_date" value="{{ old('end_date', $employment->end_date?->toDateString()) }}">@error('end_date')<p class="field-error">{{ $message }}</p>@enderror</div>
                            <div class="field sm:col-span-2 lg:col-span-6"><label for="description_{{ $employment->id }}">Description <span class="font-normal text-muted">Optional</span></label><textarea id="description_{{ $employment->id }}" name="description" rows="3" maxlength="2000">{{ old('description', $employment->description) }}</textarea>@error('description')<p class="field-error">{{ $message }}</p>@enderror</div>
                        </div>
                        <button type="submit" class="mt-5 bg-forest px-4 py-2.5 text-sm font-semibold text-white hover:bg-forest-dark">Save changes</button>
                    </form>
                </article>
            @endforeach
        </div>

        @php($newEmploymentFormId = 'new-employment-form')
        <form id="{{ $newEmploymentFormId }}" method="POST" action="{{ route('profile.employments.store') }}" class="mt-5 overflow-hidden rounded-md border border-line bg-surface">
            @csrf
            <header class="flex flex-wrap items-center justify-between gap-3 border-b border-line px-4 py-3 sm:px-5">
                <div class="flex items-center gap-3">
                    <span class="grid size-6 place-items-center rounded-full bg-forest text-xs font-semibold text-white">{{ $employmentRecords->count() + 1 }}</span>
                    <h3 class="text-sm font-semibold">New Employment Entry</h3>
                    <span class="rounded-full bg-mint px-2 py-1 text-[10px] font-semibold text-forest">Current</span>
                </div>
                <div class="flex items-center gap-3">
                    <input type="hidden" name="is_current" value="0">
                    <label class="inline-flex items-center gap-1.5 text-xs text-muted"><input type="checkbox" name="is_current" value="1" @checked(old('is_current', true))> Current</label>
                    <button type="reset" title="Clear new entry" aria-label="Clear new entry" class="grid size-7 place-items-center rounded bg-danger/15 text-danger transition hover:bg-danger/25"><svg aria-hidden="true" class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18M8 6V4h8v2m3 0-1 14H6L5 6m4 4v6m6-6v6" /></svg></button>
                </div>
            </header>
            <div class="grid gap-x-3 gap-y-4 p-4 sm:grid-cols-2 sm:p-5 lg:grid-cols-6">
                <div class="field lg:col-span-3"><label for="new_employment_status">Employment status <span class="text-danger">*</span></label><select id="new_employment_status" name="employment_status" required>@foreach (\App\Models\Employment::EMPLOYMENT_STATUSES as $value => $label)<option value="{{ $value }}" @selected(old('employment_status', 'employed_local') === $value)>{{ $label }}</option>@endforeach</select>@error('employment_status')<p class="field-error">{{ $message }}</p>@enderror</div>
                <div class="field lg:col-span-3"><label for="new_sector">Sector</label><select id="new_sector" name="sector"><option value="">Select sector</option>@foreach (\App\Models\Employment::SECTORS as $value => $label)<option value="{{ $value }}" @selected(old('sector', 'private') === $value)>{{ $label }}</option>@endforeach</select>@error('sector')<p class="field-error">{{ $message }}</p>@enderror</div>
                <div class="field sm:col-span-1 lg:col-span-3"><label for="new_employer">Company / employer name</label><input id="new_employer" name="employer" value="{{ old('employer') }}" placeholder="Company name" required>@error('employer')<p class="field-error">{{ $message }}</p>@enderror</div>
                <div class="field sm:col-span-1 lg:col-span-3"><label for="new_job_title">Job title / position</label><input id="new_job_title" name="job_title" value="{{ old('job_title') }}" placeholder="e.g. Software Developer" required>@error('job_title')<p class="field-error">{{ $message }}</p>@enderror</div>
                <div class="field lg:col-span-2"><label for="new_industry">Industry</label><input id="new_industry" name="industry" value="{{ old('industry') }}" placeholder="e.g. Information Technology">@error('industry')<p class="field-error">{{ $message }}</p>@enderror</div>
                <div class="field lg:col-span-2"><label for="new_job_level">Job level</label><select id="new_job_level" name="job_level"><option value="">Select level</option>@foreach (\App\Models\Employment::JOB_LEVELS as $value => $label)<option value="{{ $value }}" @selected(old('job_level', 'rank_file') === $value)>{{ $label }}</option>@endforeach</select>@error('job_level')<p class="field-error">{{ $message }}</p>@enderror</div>
                <div class="field lg:col-span-2"><label for="new_monthly_income">Monthly income</label><select id="new_monthly_income" name="monthly_income">@foreach (\App\Models\Employment::MONTHLY_INCOME_RANGES as $value => $label)<option value="{{ $value }}" @selected(old('monthly_income', 'prefer_not_to_say') === $value)>{{ $label }}</option>@endforeach</select>@error('monthly_income')<p class="field-error">{{ $message }}</p>@enderror</div>
                <div class="field lg:col-span-2"><label for="new_location">Work location</label><input id="new_location" name="location" value="{{ old('location') }}" placeholder="City, Province">@error('location')<p class="field-error">{{ $message }}</p>@enderror</div>
                <div class="field lg:col-span-2"><label for="new_country">Country</label><input id="new_country" name="country" value="{{ old('country', 'Philippines') }}">@error('country')<p class="field-error">{{ $message }}</p>@enderror</div>
                <div class="field lg:col-span-2"><label for="new_months_to_employment">Months to employment</label><input id="new_months_to_employment" type="number" min="0" max="600" name="months_to_employment" value="{{ old('months_to_employment') }}" placeholder="From graduation">@error('months_to_employment')<p class="field-error">{{ $message }}</p>@enderror</div>
                <div class="field lg:col-span-2"><label for="new_course_relevance">Course relevance</label><select id="new_course_relevance" name="course_relevance">@foreach (\App\Models\Employment::COURSE_RELEVANCE_OPTIONS as $value => $label)<option value="{{ $value }}" @selected(old('course_relevance', 'not_specified') === $value)>{{ $label }}</option>@endforeach</select>@error('course_relevance')<p class="field-error">{{ $message }}</p>@enderror</div>
                <div class="field lg:col-span-2"><label for="new_start_date">Date started</label><input id="new_start_date" type="date" name="start_date" value="{{ old('start_date') }}" required>@error('start_date')<p class="field-error">{{ $message }}</p>@enderror</div>
                <div class="field lg:col-span-2"><label for="new_end_date">Date ended</label><input id="new_end_date" type="date" name="end_date" value="{{ old('end_date') }}">@error('end_date')<p class="field-error">{{ $message }}</p>@enderror</div>
                <div class="field sm:col-span-2 lg:col-span-6"><label for="new_description">Description <span class="font-normal text-muted">Optional</span></label><textarea id="new_description" name="description" rows="3" maxlength="2000">{{ old('description') }}</textarea>@error('description')<p class="field-error">{{ $message }}</p>@enderror</div>
            </div>
            <div class="border-t border-line px-4 py-3 sm:px-5"><button type="submit" class="bg-forest px-5 py-2.5 text-sm font-semibold text-white hover:bg-forest-dark">Add employment</button></div>
        </form>
    </section>
</div>
@endsection
