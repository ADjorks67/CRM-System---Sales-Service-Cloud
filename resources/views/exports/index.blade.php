@extends('layouts.app')

@section('title', 'Export Data — '.config('app.name'))

@section('content')
    <div class="mb-4 flex flex-wrap items-end justify-between gap-3">
        <div>
            <h1>Export Data</h1>
            <p class="text-sm text-text/70">Download visible records as CSV (data export). Report Excel/PDF export is separate.</p>
        </div>
        <a href="{{ route('imports.index') }}" class="inline-flex min-h-11 items-center rounded border border-black/20 bg-card px-4 py-2 text-sm no-underline">Import CSV</a>
    </div>

    <ul class="divide-y divide-black/10 rounded bg-card shadow-[var(--shadow-card)]">
        @foreach ($objects as $key => $label)
            <li class="flex flex-wrap items-center justify-between gap-2 px-4 py-3 text-sm">
                <span class="font-medium">{{ $label }}</span>
                <a href="{{ route('exports.download', $key) }}" class="text-secondary no-underline">Download CSV</a>
            </li>
        @endforeach
    </ul>
@endsection
