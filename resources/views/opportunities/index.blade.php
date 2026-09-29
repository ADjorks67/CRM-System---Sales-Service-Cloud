@extends('layouts.app')

@section('title', 'Opportunities — '.config('app.name'))

@section('content')
    <div class="mb-4 flex flex-wrap items-end justify-between gap-3">
        <div>
            <h1>Opportunities</h1>
            <p class="text-sm text-text/70">
                @if ($viewMode === 'archived')
                    Archived opportunities.
                @else
                    Active pipeline (FR-OPP-001).
                @endif
            </p>
        </div>
        <div class="flex flex-wrap gap-2">
            @if ($viewMode === 'archived')
                <a href="{{ route('opportunities.index') }}" class="inline-flex min-h-11 items-center rounded border border-black/20 bg-card px-4 py-2 text-sm no-underline">Active</a>
            @else
                <a href="{{ route('opportunities.index', ['view' => 'archived']) }}" class="inline-flex min-h-11 items-center rounded border border-black/20 bg-card px-4 py-2 text-sm no-underline">Archived</a>
            @endif
            @can('create', App\Models\Opportunity::class)
                <a href="{{ route('opportunities.create') }}" class="inline-flex min-h-11 items-center rounded bg-secondary px-4 py-2 text-sm font-semibold text-white no-underline hover:bg-secondary/90">
                    New Opportunity
                </a>
            @endcan
        </div>
    </div>

    <form method="get" action="{{ route('opportunities.index') }}" class="mb-4 flex flex-wrap gap-2">
        @if ($viewMode === 'archived')
            <input type="hidden" name="view" value="archived">
        @endif
        <label for="opportunity-search" class="sr-only">Search opportunities</label>
        <input id="opportunity-search" type="search" name="q" value="{{ request('q') }}" placeholder="Search opportunities…" class="min-h-11 min-w-[14rem] flex-1 rounded border border-black/20 bg-card px-3 py-2 text-sm">
        <button type="submit" class="inline-flex min-h-11 items-center rounded border border-black/20 bg-card px-4 py-2 text-sm">Search</button>
    </form>

    @php
        $columnMap = [
            'name' => 'Opportunity Name',
            'account' => 'Account',
            'stage' => 'Stage',
            'amount' => 'Amount',
            'close_date' => 'Close Date',
            'probability' => 'Probability',
            'lead_source' => 'Lead Source',
            'owner' => 'Owner',
            'updated_at' => 'Last Modified',
        ];
        $visible = collect($columns)
            ->filter(fn ($key) => isset($columnMap[$key]))
            ->mapWithKeys(fn ($key) => [$key => $columnMap[$key]])
            ->all();
        $visible = ['select' => ''] + $visible + ['actions' => 'Actions'];
        $leadSources = \App\Support\PicklistOptions::options('lead_source');
    @endphp

    <form method="post" action="{{ route('opportunities.bulk') }}" id="opportunities-bulk-form">
        @csrf
        <div class="mb-3 flex flex-wrap items-end gap-2">
            <x-form-field name="action" label="Bulk action" :options="['change_owner' => 'Change Owner', 'archive' => 'Archive', 'delete' => 'Delete']" class="min-w-[12rem]" />
            <x-form-field name="owner_id" label="New owner" :options="$owners->mapWithKeys(fn ($u) => [$u->id => $u->name])->all()" class="min-w-[12rem]" />
            <button type="submit" class="inline-flex min-h-11 items-center rounded border border-black/20 bg-card px-4 py-2 text-sm" data-confirm="Apply bulk action to selected opportunities?">Apply</button>
        </div>

        <x-data-table
            :columns="$visible"
            :has-rows="$opportunities->count() > 0"
            :paginator="$opportunities"
            :sort="$sort"
            :direction="$direction"
            :sortable="['name', 'stage', 'amount', 'close_date', 'updated_at']"
            empty="No opportunities yet."
        >
            @foreach ($opportunities as $index => $opportunity)
                <tr @class(['border-t border-black/10', 'bg-page/60' => $index % 2 === 1, 'hover:bg-secondary/5'])>
                    <td class="px-3 py-3">
                        <input type="checkbox" name="ids[]" value="{{ $opportunity->id }}" class="size-4 rounded border-black/20" aria-label="Select {{ $opportunity->name }}">
                    </td>
                    @foreach ($columns as $key)
                        @continue(! isset($columnMap[$key]))
                        <td class="px-3 py-3">
                            @switch($key)
                                @case('name')
                                    <a href="{{ route('opportunities.show', $opportunity) }}" class="font-medium">{{ $opportunity->name }}</a>
                                    @break
                                @case('account')
                                    @if ($opportunity->account)
                                        <a href="{{ route('accounts.show', $opportunity->account) }}">{{ $opportunity->account->name }}</a>
                                    @else
                                        —
                                    @endif
                                    @break
                                @case('stage')
                                    {{ $stages[$opportunity->stage] ?? $opportunity->stage }}
                                    @break
                                @case('amount')
                                    {{ $opportunity->amount !== null ? number_format((float) $opportunity->amount, 2) : '—' }}
                                    @break
                                @case('close_date')
                                    {{ $opportunity->close_date?->toDateString() ?? '—' }}
                                    @break
                                @case('probability')
                                    {{ $opportunity->probability }}%
                                    @break
                                @case('lead_source')
                                    {{ $leadSources[$opportunity->lead_source] ?? ($opportunity->lead_source ?: '—') }}
                                    @break
                                @case('owner')
                                    {{ $opportunity->owner?->name ?? '—' }}
                                    @break
                                @case('updated_at')
                                    {{ $opportunity->updated_at?->toDateString() ?? '—' }}
                                    @break
                                @default
                                    {{ data_get($opportunity, $key) ?: '—' }}
                            @endswitch
                        </td>
                    @endforeach
                    <td class="px-3 py-3">
                        @can('update', $opportunity)
                            <a href="{{ route('opportunities.edit', $opportunity) }}" class="text-sm">Edit</a>
                        @endcan
                    </td>
                </tr>
            @endforeach
        </x-data-table>
    </form>
@endsection
