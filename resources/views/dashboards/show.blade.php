@extends('layouts.app')

@section('title', $dashboard->name.' — '.config('app.name'))

@section('content')
    <x-breadcrumbs :items="[
        ['label' => 'Dashboards', 'url' => route('dashboards.index')],
        ['label' => $dashboard->name],
    ]" />

    <div class="mb-4 flex flex-wrap items-end justify-between gap-3">
        <div>
            <h1>{{ $dashboard->name }}</h1>
            @if ($dashboard->refresh_interval_minutes)
                <p class="text-sm text-text/70">Auto-refresh every {{ $dashboard->refresh_interval_minutes }} minutes</p>
            @endif
        </div>
        <div class="flex flex-wrap gap-2">
            <button
                type="button"
                id="dashboard-refresh-btn"
                class="inline-flex min-h-11 items-center rounded border border-black/20 px-4 py-2 text-sm"
                title="Refresh widgets"
                aria-label="Refresh dashboard widgets"
                data-refresh-url="{{ route('dashboards.refresh', $dashboard) }}"
                data-refresh-interval="{{ $dashboard->refresh_interval_minutes ?? '' }}"
            >Refresh</button>
            <button type="button" class="inline-flex min-h-11 items-center rounded border border-black/20 px-4 py-2 text-sm" onclick="window.print()" title="Print dashboard" aria-label="Print dashboard">Print</button>
            @can('update', $dashboard)
                <a href="{{ route('dashboards.edit', $dashboard) }}" class="inline-flex min-h-11 items-center rounded bg-secondary px-4 py-2 text-sm font-semibold text-white no-underline">Edit</a>
            @endcan
        </div>
    </div>

    <form method="post" action="{{ route('dashboards.filters.store') }}" class="mb-4 flex flex-wrap items-end gap-2 rounded bg-card p-4 shadow-[var(--shadow-card)]">
        @csrf
        <input type="hidden" name="redirect" value="{{ route('dashboards.show', $dashboard) }}">
        <x-form-field name="date_from" label="From" type="date" :value="$filters['date_from']" />
        <x-form-field name="date_to" label="To" type="date" :value="$filters['date_to']" />
        <x-form-field name="owner_id" label="Owner" :value="$filters['owner_id']" :options="['' => 'All owners'] + $owners->mapWithKeys(fn ($u) => [$u->id => $u->name])->all()" />
        <button type="submit" class="inline-flex min-h-11 items-center rounded border border-black/20 px-4 py-2 text-sm">Apply filters</button>
    </form>

    <div id="dashboard-grid" class="grid gap-4" style="grid-template-columns: repeat(12, minmax(0, 1fr));">
        @include('dashboards.partials.widget-grid', [
            'dashboard' => $dashboard,
            'widgetPayloads' => $widgetPayloads,
        ])
    </div>
@endsection

@push('scripts')
    @vite('resources/js/dashboard-refresh.js')
@endpush
