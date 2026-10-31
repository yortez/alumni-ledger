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

    @if ($canExportAggregate)
        <section aria-labelledby="employment-outcomes-heading" class="panel overflow-hidden">
            <div class="border-b border-hairline px-4 py-4 sm:px-6">
                <h2 id="employment-outcomes-heading" class="text-title font-semibold">Outcome distribution</h2>
                <p class="mt-1 text-meta text-ink-tertiary">{{ number_format($graduateCount) }} graduates in the selected cohort. Missing records are counted as not reported.</p>
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
    @endif
</div>
@endsection
