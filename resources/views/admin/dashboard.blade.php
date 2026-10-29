@extends('layouts.app')

@section('title', $adminRole->name.' dashboard')

@section('content')
<div class="mx-auto w-full max-w-[1440px] px-5 pb-16 pt-8 sm:px-8 xl:px-20">
    <div class="flex flex-col gap-1 sm:flex-row sm:items-baseline sm:justify-between sm:gap-6">
        <h1 class="text-display font-semibold">Overview</h1>
        <p class="text-body text-ink-tertiary">{{ $adminRole->name }}</p>
    </div>

    @if (empty($workspaces))
        <p class="panel mt-6 px-6 py-10 text-caption text-ink-tertiary">This role does not have any module permissions yet.</p>
    @else
        <div class="mt-6 grid items-start gap-6 xl:grid-cols-[minmax(0,840fr)_minmax(0,416fr)]">
            <div class="space-y-6">
                @isset($metrics['graduateRecords'])
                    <section aria-labelledby="graduates-panel" class="panel flex min-h-50 flex-col justify-between p-6">
                        <div class="flex flex-wrap items-start justify-between gap-4">
                            <h2 id="graduates-panel" class="text-title font-semibold">Graduates</h2>
                            <a href="{{ route($workspaces['graduates']['route']) }}" class="inline-flex items-center gap-1 text-caption font-semibold text-accent transition hover:text-accent-strong">{{ $workspaces['graduates']['title'] }}<x-chevron /></a>
                        </div>

                        <dl class="mt-6 flex flex-wrap items-start gap-x-16 gap-y-6">
                            <div>
                                <dd class="text-display font-semibold tabular-nums">{{ number_format($metrics['graduateRecords']) }}</dd>
                                <dt class="mt-2 text-caption text-ink-tertiary">Graduate records</dt>
                            </div>
                            <div>
                                <dd class="text-display font-semibold tabular-nums">{{ number_format($metrics['accountsClaimed']) }}</dd>
                                <dt class="mt-2 text-caption text-ink-tertiary">Accounts claimed</dt>
                            </div>
                            <div>
                                <dd class="text-display font-semibold tabular-nums">{{ number_format($metrics['applicantProfiles']) }}</dd>
                                <dt class="mt-2 text-caption text-ink-tertiary">Applicant profiles</dt>
                            </div>
                        </dl>
                    </section>
                @endisset

                @if (isset($metrics['openSurveys']) || isset($metrics['activeJobs']))
                    <div class="grid gap-6 sm:grid-cols-2">
                        @isset($metrics['openSurveys'])
                            <section aria-labelledby="surveys-panel" class="panel flex min-h-46 flex-col p-6">
                                <div class="flex flex-wrap items-start justify-between gap-3">
                                    <h2 id="surveys-panel" class="text-small font-semibold">Surveys</h2>
                                    <a href="{{ route($workspaces['surveys']['route']) }}" class="inline-flex items-center gap-1 whitespace-nowrap text-caption font-semibold text-accent transition hover:text-accent-strong">{{ $workspaces['surveys']['title'] }}<x-chevron /></a>
                                </div>
                                <p class="mt-8 text-stat font-semibold tabular-nums">{{ number_format($metrics['openSurveys']) }}</p>
                                <p class="mt-2 text-caption text-ink-tertiary">Open surveys</p>
                                <p class="mt-auto pt-4 text-caption text-ink-secondary">{{ $workspaces['surveys']['detail'] }}</p>
                            </section>
                        @endisset

                        @isset($metrics['activeJobs'])
                            <section aria-labelledby="jobs-panel" class="panel flex min-h-46 flex-col p-6">
                                <div class="flex flex-wrap items-start justify-between gap-3">
                                    <h2 id="jobs-panel" class="text-small font-semibold">Jobs</h2>
                                    <a href="{{ route($workspaces['jobs']['route']) }}" class="inline-flex items-center gap-1 whitespace-nowrap text-caption font-semibold text-accent transition hover:text-accent-strong">{{ $workspaces['jobs']['title'] }}<x-chevron /></a>
                                </div>
                                <p class="mt-8 text-stat font-semibold tabular-nums">{{ number_format($metrics['activeJobs']) }}</p>
                                <p class="mt-2 text-caption text-ink-tertiary">Active jobs</p>
                                <p class="mt-auto pt-4 text-caption text-ink-secondary">{{ $workspaces['jobs']['detail'] }}</p>
                            </section>
                        @endisset
                    </div>
                @endif
            </div>

            <aside class="space-y-6">
                @isset($metrics['publishedAnnouncements'])
                    <section aria-labelledby="announcements-panel" class="panel flex min-h-44 flex-col p-6">
                        <h2 id="announcements-panel" class="text-title font-semibold">Announcements</h2>
                        <p class="mt-4 text-stat font-semibold tabular-nums">{{ number_format($metrics['publishedAnnouncements']) }}</p>
                        <p class="mt-2 text-caption text-ink-tertiary">Published announcements</p>
                        <p class="mt-4 text-caption text-ink-secondary">{{ $workspaces['announcements']['detail'] }}</p>
                        <a href="{{ route($workspaces['announcements']['route']) }}" class="mt-auto inline-flex items-center gap-1 pt-4 text-caption font-semibold text-accent transition hover:text-accent-strong">{{ $workspaces['announcements']['title'] }}<x-chevron /></a>
                    </section>
                @endisset

                @if (isset($workspaces['manage_admins']) || isset($workspaces['manage_roles']))
                    <section aria-labelledby="access-panel" class="panel p-6">
                        <h2 id="access-panel" class="text-title font-semibold">Administrator access</h2>

                        <div class="mt-3 divide-y divide-divider-soft border-t border-hairline">
                            @isset($workspaces['manage_admins'])
                                <a href="{{ route($workspaces['manage_admins']['route']) }}" class="flex items-start justify-between gap-4 py-4">
                                    <span class="min-w-0">
                                        <span class="block text-caption font-semibold text-ink">{{ $workspaces['manage_admins']['title'] }}</span>
                                        <span class="mt-1 block text-meta text-ink-tertiary">{{ $workspaces['manage_admins']['detail'] }}</span>
                                    </span>
                                    <x-chevron class="mt-1 text-accent" />
                                </a>
                            @endisset

                            @isset($workspaces['manage_roles'])
                                <a href="{{ route($workspaces['manage_roles']['route']) }}" class="flex items-start justify-between gap-4 py-4">
                                    <span class="min-w-0">
                                        <span class="block text-caption font-semibold text-ink">{{ $workspaces['manage_roles']['title'] }}</span>
                                        <span class="mt-1 block text-meta text-ink-tertiary">{{ $workspaces['manage_roles']['detail'] }}</span>
                                    </span>
                                    <x-chevron class="mt-1 text-accent" />
                                </a>
                            @endisset
                        </div>
                    </section>
                @endif
            </aside>
        </div>
    @endif
</div>
@endsection
