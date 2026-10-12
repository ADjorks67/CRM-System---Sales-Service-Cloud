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
                <p class="text-sm text-muted">Auto-refresh every {{ $dashboard->refresh_interval_minutes }} minutes</p>
            @endif
        </div>
        <div class="flex flex-wrap gap-2">
            <button
                type="button"
                id="dashboard-refresh-btn"
                class="crm-btn-secondary"
                title="Refresh widgets"
                aria-label="Refresh dashboard widgets"
                data-refresh-url="{{ route('dashboards.refresh', $dashboard) }}"
                data-refresh-interval="{{ $dashboard->refresh_interval_minutes ?? '' }}"
            >Refresh</button>
            <button type="button" class="crm-btn-secondary" onclick="window.print()" title="Print dashboard" aria-label="Print dashboard">Print</button>
            @can('update', $dashboard)
                <a href="{{ route('dashboards.edit', $dashboard) }}" class="crm-btn-secondary">Edit</a>
            @endcan
        </div>
    </div>

    <form method="post" action="{{ route('dashboards.filters.store') }}" class="mb-4 flex flex-wrap items-end gap-2 crm-card p-4 sm:p-5">
        @csrf
        <input type="hidden" name="redirect" value="{{ route('dashboards.show', $dashboard) }}">
        <x-form-field name="date_from" label="From" type="date" :value="$filters['date_from']" />
        <x-form-field name="date_to" label="To" type="date" :value="$filters['date_to']" />
        <x-form-field name="owner_id" label="Owner" :value="$filters['owner_id']" :options="['' => 'All owners'] + $owners->mapWithKeys(fn ($u) => [$u->id => $u->name])->all()" />
        <button type="submit" class="crm-btn-secondary">Apply filters</button>
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
