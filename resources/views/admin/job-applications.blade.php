@extends('layouts.app')

@section('title', 'Job applications')

@section('content')
<div class="mx-auto w-full max-w-[1440px] px-5 pb-16 pt-8 sm:px-8 xl:px-20">
    <a href="{{ route('admin.jobs.index') }}" class="inline-flex items-center gap-1 text-caption font-semibold text-ink-tertiary transition hover:text-ink"><x-chevron class="rotate-180" />All job postings</a>

    <div class="mt-5 border-b border-hairline pb-6">
        <h1 class="text-display font-semibold wrap-break-word">{{ $job->title }}</h1>
        <p class="mt-3 flex flex-wrap items-center gap-x-3 gap-y-1 text-body text-ink-tertiary">
            <span class="text-ink-secondary">{{ $job->company }}</span>
            <span>{{ $job->location }}</span>
            <span class="tabular-nums">{{ $applications->total() }} {{ $applications->total() === 1 ? 'application' : 'applications' }}</span>
        </p>
    </div>

    <section aria-label="Applications" class="panel mt-6 overflow-hidden">
        <ul>
            @forelse ($applications as $application)
                @php($status = $application->status)
                @php($currentEmployment = $application->user->profile?->currentEmployment)
                @php($statusTone = match ($status) {
                    'approved' => 'bg-success/10 text-success',
                    'rejected' => 'bg-danger/10 text-danger',
                    'interview_scheduled' => 'bg-warning/10 text-warning',
                    'reviewing' => 'bg-mint text-accent',
                    default => 'bg-canvas text-ink-tertiary',
                })

                <li class="border-t border-divider-soft first:border-t-0">
                    <details class="group">
                        <summary class="flex cursor-pointer list-none items-center gap-3 px-4 py-3.5 transition-colors [&::-webkit-details-marker]:hidden hover:bg-canvas sm:px-5">
                            <span aria-hidden="true" class="grid size-9 shrink-0 place-items-center rounded-full bg-canvas text-meta font-semibold text-ink-tertiary">{{ strtoupper(substr($application->user->name, 0, 1)) }}</span>

                            <span class="min-w-0 flex-1">
                                <span class="flex flex-wrap items-center gap-x-3 gap-y-1">
                                    <span class="text-small font-semibold text-ink wrap-break-word">{{ $application->user->name }}</span>
                                    <span class="shrink-0 rounded-full px-2 py-0.5 text-micro font-semibold {{ $statusTone }}">{{ $statusLabels[$status] ?? ucfirst(str_replace('_', ' ', $status)) }}</span>
                                </span>

                                <span class="mt-0.5 flex flex-wrap items-center gap-x-3 gap-y-0.5 text-meta text-ink-tertiary">
                                    @if ($currentEmployment?->job_title)
                                        <span class="truncate">{{ $currentEmployment->job_title }}</span>
                                    @endif
                                    @if ($currentEmployment?->employer)
                                        <span class="truncate">{{ $currentEmployment->employer }}</span>
                                    @endif
                                    <span class="truncate">{{ $application->user->email }}</span>
                                    <span class="tabular-nums">Submitted {{ $application->submitted_at->format('j M Y') }}</span>
                                </span>
                            </span>

                            <x-chevron class="shrink-0 text-ink-tertiary transition-transform group-open:rotate-90" />
                        </summary>

                        <div class="mb-3 grid gap-4 rounded-chip bg-surface-pearl p-4">
                            @if ($application->cover_letter)
                                <p class="max-w-[68ch] whitespace-pre-line text-caption text-ink-secondary">{{ $application->cover_letter }}</p>
                            @else
                                <p class="text-caption text-ink-tertiary">No cover letter submitted.</p>
                            @endif

                            <div class="flex flex-wrap items-center gap-2">
                                <a href="{{ route('admin.jobs.applications.show', [$job, $application]) }}" class="inline-flex h-8 items-center gap-1 rounded-full bg-accent px-3 text-caption font-semibold text-white transition hover:bg-accent-strong">View profile<x-chevron /></a>

                                @foreach (['reviewing' => 'Under review', 'interview_scheduled' => 'Schedule interview', 'approved' => 'Approve', 'rejected' => 'Reject'] as $value => $label)
                                    @continue($status === $value)
                                    <form method="POST" action="{{ route('admin.jobs.applications.update', [$job, $application]) }}">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="status" value="{{ $value }}">
                                        <button type="submit" class="inline-flex h-8 items-center rounded-full border px-3 text-caption font-semibold transition {{ $value === 'rejected' ? 'border-danger/30 text-danger hover:bg-danger/10' : 'border-hairline text-ink-secondary hover:border-chip-gray hover:text-ink' }}">{{ $label }}</button>
                                    </form>
                                @endforeach
                            </div>
                        </div>
                    </details>
                </li>
            @empty
                <li class="px-5 py-12 text-center text-caption text-ink-tertiary">No applications yet.</li>
            @endforelse
        </ul>

        @if ($applications->hasPages())
            <div class="flex flex-wrap items-center justify-between gap-3 border-t border-hairline px-4 py-3 sm:px-5">
                <p class="text-meta text-ink-tertiary">Page {{ $applications->currentPage() }} of {{ $applications->lastPage() }}</p>
                <x-pagination-pills :paginator="$applications" />
            </div>
        @endif
    </section>
</div>
@endsection
