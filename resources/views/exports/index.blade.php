@extends('layouts.app')

@section('title', 'Export Data — '.config('app.name'))

@section('content')
    <div class="mb-4 flex flex-wrap items-end justify-between gap-3">
        <div>
            <h1>Export Data</h1>
            <p class="text-sm text-muted">Download visible records as CSV (data export). Report Excel/PDF export is separate.</p>
        </div>
        <a href="{{ route('imports.index') }}" class="crm-btn-secondary">Import CSV</a>
    </div>

    <ul class="divide-y divide-border rounded bg-card shadow-[var(--shadow-card)]">
        @foreach ($objects as $key => $label)
            <li class="flex flex-wrap items-center justify-between gap-2 px-4 py-3 text-sm">
                <span class="font-medium">{{ $label }}</span>
                <a href="{{ route('exports.download', $key) }}" class="text-secondary no-underline">Download CSV</a>
            </li>
        @endforeach
    </ul>
@endsection
