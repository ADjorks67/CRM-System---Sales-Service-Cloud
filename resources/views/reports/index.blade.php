@extends('layouts.app')

@section('title', 'Reports — '.config('app.name'))

@section('content')
    <div class="mb-6 flex flex-wrap items-end justify-between gap-3 border-b border-border pb-4">
        <div>
            <h1 class="font-headline">Reports</h1>
            <p class="text-sm text-muted">Pre-built reports (FR-RPT-002) and custom saved reports (FR-RPT-003).</p>
        </div>
        @can('viewAny', App\Models\SavedReport::class)
            <a href="{{ route('saved-reports.index') }}" class="crm-btn-secondary">Custom Reports</a>
        @endcan
    </div>

    <div class="grid gap-4">
        @foreach ($groups as $category => $reports)
            <section class="crm-card p-4 sm:p-5">
                <h2 class="mb-3 text-base font-semibold text-primary">{{ $category }}</h2>
                <ul class="divide-y divide-border text-sm">
                    @foreach ($reports as $report)
                        <li class="flex flex-wrap items-center justify-between gap-2 py-2">
                            <div>
                                <a href="{{ route('reports.show', $report->key()) }}" class="font-medium">{{ $report->title() }}</a>
                                @if ($report->description() !== '')
                                    <p class="text-muted">{{ $report->description() }}</p>
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
