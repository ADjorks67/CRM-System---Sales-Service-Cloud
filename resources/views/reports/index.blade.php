@extends('layouts.app')

@section('title', 'Reports — '.config('app.name'))

@section('content')
    <div class="mb-4">
        <h1>Reports</h1>
        <p class="text-sm text-text/70">Pre-built reports for leads, opportunities, and cases (FR-RPT-002). Export and builder arrive in Phase 4.</p>
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
