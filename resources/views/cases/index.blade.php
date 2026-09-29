@extends('layouts.app')

@section('title', 'Cases — '.config('app.name'))

@section('content')
    <div class="mb-4 flex flex-wrap items-end justify-between gap-3">
        <div>
            <h1>Cases</h1>
            <p class="text-sm text-text/70">Service cases (FR-CASE-001).</p>
        </div>
        @can('create', App\Models\CrmCase::class)
            <a href="{{ route('cases.create') }}" class="inline-flex min-h-11 items-center rounded bg-secondary px-4 py-2 text-sm font-semibold text-white no-underline hover:bg-secondary/90">
                New Case
            </a>
        @endcan
    </div>

    <nav class="mb-4 flex flex-wrap gap-2 text-sm" aria-label="Case list views">
        @php
            $views = [
                'my_open' => 'My Open Cases',
                'all_open' => 'All Open Cases',
                'recently_closed' => 'Recently Closed',
            ];
        @endphp
        @foreach ($views as $key => $label)
            <a
                href="{{ route('cases.index', array_merge(request()->except('page'), ['view' => $key])) }}"
                @class([
                    'inline-flex min-h-11 items-center rounded px-4 py-2 no-underline',
                    'bg-secondary text-white font-semibold' => $view === $key,
                    'border border-black/20 bg-card text-text' => $view !== $key,
                ])
            >{{ $label }}</a>
        @endforeach
    </nav>

    <form method="get" action="{{ route('cases.index') }}" class="mb-4 flex flex-wrap gap-2">
        <input type="hidden" name="view" value="{{ $view }}">
        <label for="case-search" class="sr-only">Search cases</label>
        <input id="case-search" type="search" name="q" value="{{ request('q') }}" placeholder="Search cases…" class="min-h-11 min-w-[14rem] flex-1 rounded border border-black/20 bg-card px-3 py-2 text-sm">
        <button type="submit" class="inline-flex min-h-11 items-center rounded border border-black/20 bg-card px-4 py-2 text-sm">Search</button>
    </form>

    @php
        $columnMap = [
            'case_number' => 'Case Number',
            'subject' => 'Subject',
            'status' => 'Status',
            'priority' => 'Priority',
            'origin' => 'Origin',
            'account' => 'Account',
            'contact' => 'Contact',
            'owner' => 'Owner',
            'updated_at' => 'Last Modified',
        ];
        $visible = collect($columns)
            ->filter(fn ($key) => isset($columnMap[$key]))
            ->mapWithKeys(fn ($key) => [$key => $columnMap[$key]])
            ->all();
        $visible = ['select' => ''] + $visible + ['actions' => 'Actions'];
        $statusLabels = \App\Support\PicklistOptions::options('case_status');
        $priorityLabels = \App\Support\PicklistOptions::options('case_priority');
        $originLabels = \App\Support\PicklistOptions::options('case_origin');
    @endphp

    <form method="post" action="{{ route('cases.bulk') }}">
        @csrf
        <div class="mb-3 flex flex-wrap items-end gap-2">
            <x-form-field name="action" label="Bulk action" :options="['change_owner' => 'Change Owner', 'change_status' => 'Change Status', 'delete' => 'Delete']" class="min-w-[12rem]" />
            <x-form-field name="owner_id" label="New owner" :options="$owners->mapWithKeys(fn ($u) => [$u->id => $u->name])->all()" class="min-w-[12rem]" />
            <x-form-field name="status" label="New status" :options="$statuses" class="min-w-[12rem]" />
            <button type="submit" class="inline-flex min-h-11 items-center rounded border border-black/20 bg-card px-4 py-2 text-sm" onclick="return confirm('Apply bulk action to selected cases?')">Apply</button>
        </div>

        <x-data-table
            :columns="$visible"
            :has-rows="$cases->count() > 0"
            :paginator="$cases"
            :sort="$sort"
            :direction="$direction"
            :sortable="['case_number', 'subject', 'status', 'priority', 'updated_at']"
            empty="No cases in this view."
        >
            @foreach ($cases as $index => $case)
                @php
                    $priorityClass = match ($case->priority) {
                        'high' => 'text-error',
                        'medium' => 'text-warning',
                        'low' => 'text-success',
                        default => '',
                    };
                @endphp
                <tr @class(['border-t border-black/10', 'bg-page/60' => $index % 2 === 1, 'hover:bg-secondary/5'])>
                    <td class="px-3 py-3">
                        <input type="checkbox" name="ids[]" value="{{ $case->id }}" class="size-4 rounded border-black/20" aria-label="Select {{ $case->displayName() }}">
                    </td>
                    @foreach ($columns as $key)
                        @continue(! isset($columnMap[$key]))
                        <td class="px-3 py-3">
                            @switch($key)
                                @case('case_number')
                                    <a href="{{ route('cases.show', $case) }}" class="font-medium">{{ $case->case_number }}</a>
                                    @break
                                @case('subject')
                                    <a href="{{ route('cases.show', $case) }}">{{ $case->displayName() }}</a>
                                    @break
                                @case('status')
                                    {{ $statusLabels[$case->status] ?? $case->status }}
                                    @break
                                @case('priority')
                                    <span @class([$priorityClass])>{{ $priorityLabels[$case->priority] ?? $case->priority }}</span>
                                    @break
                                @case('origin')
                                    {{ $originLabels[$case->origin] ?? ($case->origin ?: '—') }}
                                    @break
                                @case('account')
                                    {{ $case->account?->name ?? '—' }}
                                    @break
                                @case('contact')
                                    {{ $case->contact?->displayName() ?? '—' }}
                                    @break
                                @case('owner')
                                    {{ $case->owner?->name ?? '—' }}
                                    @break
                                @case('updated_at')
                                    {{ $case->updated_at?->toDateString() ?? '—' }}
                                    @break
                                @default
                                    {{ data_get($case, $key) ?: '—' }}
                            @endswitch
                        </td>
                    @endforeach
                    <td class="px-3 py-3">
                        @can('update', $case)
                            <a href="{{ route('cases.edit', $case) }}" class="text-sm">Edit</a>
                        @endcan
                    </td>
                </tr>
            @endforeach
        </x-data-table>
    </form>
@endsection
