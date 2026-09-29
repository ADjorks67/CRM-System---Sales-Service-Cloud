@extends('layouts.app')

@section('title', 'Leads — '.config('app.name'))

@section('content')
    <div class="mb-4 flex flex-wrap items-end justify-between gap-3">
        <div>
            <h1>Leads</h1>
            <p class="text-sm text-text/70">Recently viewed leads (FR-LEAD-001).</p>
        </div>
        @can('create', App\Models\Lead::class)
            <a href="{{ route('leads.create') }}" class="inline-flex min-h-11 items-center rounded bg-secondary px-4 py-2 text-sm font-semibold text-white no-underline hover:bg-secondary/90">
                New Lead
            </a>
        @endcan
    </div>

    <form method="get" action="{{ route('leads.index') }}" class="mb-4 flex flex-wrap gap-2">
        <label for="lead-search" class="sr-only">Search leads</label>
        <input id="lead-search" type="search" name="q" value="{{ request('q') }}" placeholder="Search leads…" class="min-h-11 min-w-[14rem] flex-1 rounded border border-black/20 bg-card px-3 py-2 text-sm">
        <button type="submit" class="inline-flex min-h-11 items-center rounded border border-black/20 bg-card px-4 py-2 text-sm">Search</button>
    </form>

    @php
        $columnMap = [
            'name' => 'Name',
            'company' => 'Company',
            'status' => 'Status',
            'email' => 'Email',
            'phone' => 'Phone',
            'lead_source' => 'Lead Source',
            'rating' => 'Rating',
            'owner' => 'Owner',
            'updated_at' => 'Last Modified',
        ];
        $visible = collect($columns)
            ->filter(fn ($key) => isset($columnMap[$key]))
            ->mapWithKeys(fn ($key) => [$key => $columnMap[$key]])
            ->all();
        $visible = ['select' => ''] + $visible + ['actions' => 'Actions'];
        $statusLabels = \App\Support\PicklistOptions::options('lead_status');
        $sourceLabels = \App\Support\PicklistOptions::options('lead_source');
        $ratingLabels = \App\Support\PicklistOptions::options('rating');
    @endphp

    <form method="post" action="{{ route('leads.bulk') }}">
        @csrf
        <div class="mb-3 flex flex-wrap items-end gap-2">
            <x-form-field name="action" label="Bulk action" :options="['change_owner' => 'Change Owner', 'change_status' => 'Change Status', 'delete' => 'Delete']" class="min-w-[12rem]" />
            <x-form-field name="owner_id" label="New owner" :options="$owners->mapWithKeys(fn ($u) => [$u->id => $u->name])->all()" class="min-w-[12rem]" />
            <x-form-field name="status" label="New status" :options="$statuses" class="min-w-[12rem]" />
            <button type="submit" class="inline-flex min-h-11 items-center rounded border border-black/20 bg-card px-4 py-2 text-sm" onclick="return confirm('Apply bulk action to selected leads?')">Apply</button>
        </div>

        <x-data-table
            :columns="$visible"
            :has-rows="$leads->count() > 0"
            :paginator="$leads"
            :sort="$sort"
            :direction="$direction"
            :sortable="['last_name', 'company', 'status', 'email', 'updated_at']"
            empty="No leads yet."
        >
            @foreach ($leads as $index => $lead)
                <tr @class(['border-t border-black/10', 'bg-page/60' => $index % 2 === 1, 'hover:bg-secondary/5'])>
                    <td class="px-3 py-3">
                        <input type="checkbox" name="ids[]" value="{{ $lead->id }}" class="size-4 rounded border-black/20" aria-label="Select {{ $lead->displayName() }}">
                    </td>
                    @foreach ($columns as $key)
                        @continue(! isset($columnMap[$key]))
                        <td class="px-3 py-3">
                            @switch($key)
                                @case('name')
                                    <a href="{{ route('leads.show', $lead) }}" class="font-medium">{{ $lead->displayName() }}</a>
                                    @break
                                @case('status')
                                    {{ $statusLabels[$lead->status?->value] ?? $lead->status?->label() ?? '—' }}
                                    @break
                                @case('lead_source')
                                    {{ $sourceLabels[$lead->lead_source] ?? ($lead->lead_source ?: '—') }}
                                    @break
                                @case('rating')
                                    {{ $ratingLabels[$lead->rating] ?? ($lead->rating ?: '—') }}
                                    @break
                                @case('owner')
                                    {{ $lead->owner?->name ?? '—' }}
                                    @break
                                @case('updated_at')
                                    {{ $lead->updated_at?->toDateString() ?? '—' }}
                                    @break
                                @default
                                    {{ data_get($lead, $key) ?: '—' }}
                            @endswitch
                        </td>
                    @endforeach
                    <td class="px-3 py-3">
                        @can('update', $lead)
                            <a href="{{ route('leads.edit', $lead) }}" class="text-sm">Edit</a>
                        @endcan
                    </td>
                </tr>
            @endforeach
        </x-data-table>
    </form>
@endsection
