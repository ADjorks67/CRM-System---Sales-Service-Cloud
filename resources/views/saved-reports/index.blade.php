@extends('layouts.app')

@section('title', 'Custom Reports — '.config('app.name'))

@section('content')
    <div class="mb-4 flex flex-wrap items-end justify-between gap-3">
        <div>
            <p class="text-sm"><a href="{{ route('reports.index') }}" class="text-secondary no-underline">Reports</a></p>
            <h1>Custom Reports</h1>
            <p class="text-sm text-text/70">Saved report builder (FR-RPT-003).</p>
        </div>
        @can('create', App\Models\SavedReport::class)
            <a href="{{ route('saved-reports.create') }}" class="inline-flex min-h-11 items-center rounded bg-secondary px-4 py-2 text-sm font-semibold text-white no-underline">New Report</a>
        @endcan
    </div>

    <div class="grid gap-4">
        @forelse ($groups as $folder => $reports)
            <section class="rounded bg-card p-4 shadow-[var(--shadow-card)]">
                <h2 class="mb-3 text-base font-semibold text-primary">{{ ucfirst($folder) }}</h2>
                <ul class="divide-y divide-black/10 text-sm">
                    @foreach ($reports as $report)
                        <li class="flex flex-wrap items-center justify-between gap-2 py-2">
                            <div>
                                <a href="{{ route('saved-reports.show', $report) }}" class="font-medium">{{ $report->name }}</a>
                                <p class="text-text/60">{{ ucfirst($report->report_type) }} · {{ $report->owner?->name ?? '—' }}</p>
                            </div>
                            <div class="flex gap-3">
                                <a href="{{ route('saved-reports.show', $report) }}" class="text-secondary no-underline">Run</a>
                                @can('update', $report)
                                    <a href="{{ route('saved-reports.edit', $report) }}" class="text-secondary no-underline">Edit</a>
                                @endcan
                            </div>
                        </li>
                    @endforeach
                </ul>
            </section>
        @empty
            <p class="text-sm text-text/70">No custom reports yet.</p>
        @endforelse
    </div>
@endsection
