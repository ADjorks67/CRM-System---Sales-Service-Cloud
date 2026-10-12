@extends('layouts.app')

@section('title', $savedReport->name.' — '.config('app.name'))

@section('content')
    <x-breadcrumbs :items="[
        ['label' => 'Custom Reports', 'url' => route('saved-reports.index')],
        ['label' => $savedReport->name],
    ]" />

    <div class="mb-4 flex flex-wrap items-end justify-between gap-3">
        <div>
            <h1>{{ $savedReport->name }}</h1>
            <p class="text-sm text-muted">{{ $result['rows']->count() }} row(s)</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('saved-reports.export', $savedReport) }}" class="crm-btn-secondary">Download CSV</a>
            <a href="{{ route('saved-reports.print', $savedReport) }}" target="_blank" rel="noopener" class="crm-btn-secondary">Print / PDF</a>
            <a href="{{ route('subscriptions.create', $savedReport) }}" class="crm-btn-secondary">Subscribe</a>
            @can('update', $savedReport)
                <a href="{{ route('saved-reports.edit', $savedReport) }}" class="crm-btn-secondary">Edit</a>
            @endcan
        </div>
    </div>

    @if ($result['chart'] && count($result['chart']['values'] ?? []) > 0)
        <div class="mb-4 crm-card p-4 sm:p-5">
            <x-chart :type="$result['chart']['type']" :config="$result['chart']" height="14rem" :aria-label="$savedReport->name.' chart'" />
        </div>
    @endif

    <div class="overflow-x-auto rounded bg-card shadow-[var(--shadow-card)]">
        <table class="min-w-full text-left text-sm">
            <thead class="border-b border-border bg-black/[0.03]">
                <tr>
                    @foreach ($result['column_labels'] as $label)
                        <th class="px-3 py-2 font-semibold">{{ $label }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @forelse ($result['rows'] as $row)
                    <tr class="border-b border-black/5">
                        @foreach ($result['columns'] as $column)
                            <td class="px-3 py-2">{{ $row[$column] ?? '—' }}</td>
                        @endforeach
                    </tr>
                @empty
                    <tr><td class="px-3 py-4 text-muted" colspan="{{ count($result['columns']) }}">No rows match this report.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
