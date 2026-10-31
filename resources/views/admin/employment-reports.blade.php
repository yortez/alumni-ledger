@extends('layouts.app')

@section('title', 'Employment outcomes')

@section('content')
<div class="mx-auto w-full max-w-360 px-5 pb-16 pt-8 sm:px-8 xl:px-20">
    <header class="mb-5 flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="text-display font-semibold">Employment outcomes</h1>
            <p class="mt-1 text-meta text-ink-tertiary">Current outcome by graduate cohort</p>
        </div>
        <div class="flex flex-wrap gap-2">
            @if ($canExportAggregate)
                <a href="{{ route('admin.reports.employment.summary', $filters) }}" class="inline-flex min-h-9 items-center rounded-md border border-line bg-surface px-3 py-2 text-xs font-medium text-muted transition hover:border-ink hover:text-ink">Download summary CSV</a>
            @endif
            @if ($canExportDetails)
                <a href="{{ route('admin.reports.employment.details', $filters) }}" class="inline-flex min-h-9 items-center rounded-md bg-forest px-3 py-2 text-xs font-semibold text-white transition hover:bg-forest-dark">Download detailed CSV</a>
            @endif
        </div>
    </header>

    <form method="GET" action="{{ route('admin.reports.employment.index') }}" class="panel mb-4 grid gap-3 p-4 sm:grid-cols-2 lg:grid-cols-4">
        <label>
            <span class="sr-only">Program</span>
            <select name="program" class="w-full rounded-md border border-line bg-surface-raised px-3 py-2 text-xs text-ink focus:border-forest focus:outline-none">
                <option value="">All programs</option>
                @foreach ($programs as $program)
                    <option value="{{ $program }}" @selected($filters['program'] === $program)>{{ $program }}</option>
                @endforeach
            </select>
        </label>
        <label>
            <span class="sr-only">Graduation batch</span>
            <select name="batch" class="w-full rounded-md border border-line bg-surface-raised px-3 py-2 text-xs text-ink focus:border-forest focus:outline-none">
                <option value="">All batches</option>
                @foreach ($batches as $batch)
                    <option value="{{ $batch }}" @selected($filters['batch'] === (string) $batch)>{{ $batch }}</option>
                @endforeach
            </select>
        </label>
        <div class="flex flex-wrap gap-2 sm:col-span-2 lg:col-span-4">
            <button type="submit" class="inline-flex min-h-8 items-center rounded-md bg-forest px-4 py-2 text-xs font-semibold text-white transition hover:bg-forest-dark">Apply filters</button>
            <a href="{{ route('admin.reports.employment.index') }}" class="inline-flex min-h-8 items-center rounded-md border border-line bg-surface-raised px-4 py-2 text-xs font-medium text-muted transition hover:text-ink">Reset</a>
        </div>
    </form>

    @if ($canExportAggregate && $reportSuppressed)
        <section class="panel mt-4 px-4 py-5 sm:px-6" role="status">
            <h2 class="text-title font-semibold">Aggregate results hidden</h2>
            <p class="mt-2 text-caption text-ink-tertiary">At least 5 graduates are required to display aggregate results. Widen the selected cohort or choose another program or batch.</p>
        </section>
    @elseif ($canExportAggregate && $analytics)
        <section aria-label="Employment outcome metrics" class="panel mt-4 overflow-hidden">
            <dl class="grid grid-cols-2 divide-x divide-y divide-hairline sm:grid-cols-4 sm:divide-y-0">
                <div class="p-4 sm:px-5 sm:py-5">
                    <dt class="text-meta text-ink-tertiary">Graduates in cohort</dt>
                    <dd class="mt-2 text-title font-semibold tabular-nums text-ink">{{ number_format($analytics['graduate_count']) }}</dd>
                </div>
                <div class="p-4 sm:px-5 sm:py-5">
                    <dt class="text-meta text-ink-tertiary">Reporting coverage</dt>
                    <dd class="mt-2 text-title font-semibold tabular-nums text-ink">{{ $analytics['reporting_coverage']['percentage'] }}</dd>
                    <dd class="mt-1 text-meta text-ink-tertiary">{{ $analytics['reporting_coverage']['count'] }} of {{ $analytics['reporting_coverage']['denominator'] }} reported</dd>
                </div>
                <div class="p-4 sm:px-5 sm:py-5">
                    <dt class="text-meta text-ink-tertiary">Employment rate among respondents</dt>
                    <dd class="mt-2 text-title font-semibold tabular-nums text-ink">{{ $analytics['employment_rate']['percentage'] }}</dd>
                    <dd class="mt-1 text-meta text-ink-tertiary">{{ $analytics['employment_rate']['count'] }} of {{ $analytics['employment_rate']['denominator'] }} reported outcomes</dd>
                </div>
                <div class="p-4 sm:px-5 sm:py-5">
                    <dt class="text-meta text-ink-tertiary">Median time to employment</dt>
                    <dd class="mt-2 text-title font-semibold tabular-nums text-ink">{{ $analytics['time_to_employment']['median_months'] === null ? 'Not reported' : number_format($analytics['time_to_employment']['median_months'], 1).' months' }}</dd>
                    <dd class="mt-1 text-meta text-ink-tertiary">{{ number_format($analytics['time_to_employment']['sample_count']) }} employed records</dd>
                </div>
            </dl>
        </section>

        <section aria-labelledby="employment-outcomes-heading" class="panel overflow-hidden">
            <div class="border-b border-hairline px-4 py-4 sm:px-6">
                <h2 id="employment-outcomes-heading" class="text-title font-semibold">Outcome distribution</h2>
                <p class="mt-1 text-meta text-ink-tertiary">Percentages use all graduates in the selected cohort. Missing current status is counted as not reported.</p>
            </div>
            <div class="overflow-x-auto">
                <table class="data-table w-full min-w-140 border-collapse text-left text-xs">
                    <thead class="bg-surface-raised text-[10px] uppercase tracking-wide text-muted">
                        <tr><th class="px-4 py-3 font-semibold sm:px-6">Employment outcome</th><th class="px-4 py-3 text-right font-semibold">Graduates</th><th class="px-4 py-3 text-right font-semibold sm:px-6">Share of cohort</th></tr>
                    </thead>
                    <tbody class="divide-y divide-line">
                        @foreach ($outcomes as $outcome)
                            <tr>
                                <th scope="row" class="px-4 py-3 font-medium text-ink sm:px-6">{{ $outcome['label'] }}</th>
                                <td class="px-4 py-3 text-right tabular-nums text-ink">{{ number_format($outcome['count']) }}</td>
                                <td class="px-4 py-3 text-right tabular-nums text-ink sm:px-6">{{ $outcome['percentage'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>

        <section aria-labelledby="employment-characteristics-heading" class="panel mt-4 overflow-hidden">
            <div class="border-b border-hairline px-4 py-4 sm:px-6">
                <h2 id="employment-characteristics-heading" class="text-title font-semibold">Characteristics among employed graduates</h2>
                <p class="mt-1 text-meta text-ink-tertiary">Shares use all currently employed graduates as the denominator; missing values are listed separately.</p>
            </div>
            <div class="grid divide-y divide-hairline sm:grid-cols-3 sm:divide-x sm:divide-y-0">
                @foreach ($analytics['distributions'] as $distribution)
                    <section class="p-4 sm:p-5" aria-label="{{ $distribution['label'] }} distribution">
                        <h3 class="text-small font-semibold text-ink">{{ $distribution['label'] }}</h3>
                        <p class="mt-1 text-meta text-ink-tertiary">{{ number_format($distribution['denominator']) }} employed graduates</p>
                        @if ($distribution['items'] !== [])
                            <dl class="mt-4 grid gap-2">
                                @foreach ($distribution['items'] as $item)
                                    <div class="flex items-start justify-between gap-3 text-caption">
                                        <dt class="text-ink-secondary">{{ $item['label'] }}</dt>
                                        <dd class="shrink-0 text-right tabular-nums text-ink">{{ number_format($item['count']) }} <span class="text-ink-tertiary">({{ $item['percentage'] }})</span></dd>
                                    </div>
                                @endforeach
                            </dl>
                        @else
                            <p class="mt-4 text-caption text-ink-tertiary">No values reported.</p>
                        @endif
                    </section>
                @endforeach
            </div>
        </section>
    @endif
</div>
@endsection
