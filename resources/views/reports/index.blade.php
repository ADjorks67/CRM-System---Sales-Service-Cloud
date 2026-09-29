@extends('layouts.app')

@section('title', 'Reports — '.config('app.name'))

@section('content')
    <div class="mb-4 flex flex-wrap items-end justify-between gap-3">
        <div>
            <h1>Reports</h1>
            <p class="text-sm text-text/70">Pre-built reports (FR-RPT-002) and custom saved reports (FR-RPT-003).</p>
        </div>
        @can('viewAny', App\Models\SavedReport::class)
            <a href="{{ route('saved-reports.index') }}" class="inline-flex min-h-11 items-center rounded border border-black/20 px-4 py-2 text-sm no-underline">Custom Reports</a>
        @endcan
    </div>

    <div class="grid gap-4">
        @foreach ($groups as $category => $reports)
            <section class="rounded bg-card p-4 shadow-[var(--shadow-card)]">
                <h2 class="mb-3 text-base font-semibold text-primary">{{ $category }}</h2>
                <ul class="divide-y divide-black/10 text-sm">
                    @foreach ($reports as $report)
                        <li class="flex flex-wrap items-center justify-between gap-2 py-2">
                            <div>
                                <a href="{{ route('reports.show', $report->key()) }}" class="font-medium">{{ $report->title() }}</a>
                                @if ($report->description() !== '')
                                    <p class="text-text/60">{{ $report->description() }}</p>
                                @endif
                            </div>
                            <a href="{{ route('reports.show', $report->key()) }}" class="text-secondary no-underline">Run</a>
                        </li>
                    @endforeach
                </ul>
            </section>
        @endforeach
    </div>
@endsection
