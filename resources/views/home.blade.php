@extends('layouts.app')

@section('title', 'Home — '.config('app.name'))
@section('page-title', 'Home')

@section('content')
    <div class="mb-6 flex flex-col justify-between gap-4 border-b border-border pb-4 xl:flex-row xl:items-end">
        <div class="min-w-0 space-y-1">
            <div class="flex flex-wrap items-center gap-2">
                <span class="text-[0.65rem] font-semibold uppercase tracking-wider text-muted">Executive console</span>
                @if (auth()->user()->role)
                    <span class="crm-badge border border-accent-espresso/40 bg-[#5c4131]/40 text-[#e5bfa9]">
                        {{ auth()->user()->role->name }}
                    </span>
                @endif
            </div>
            <h1 class="font-headline text-2xl font-semibold tracking-tight text-primary sm:text-[1.625rem]">
                Welcome back, {{ auth()->user()->name }}
            </h1>
            <p class="text-sm text-muted">Pipeline overview for the selected period</p>
        </div>
        <form method="get" action="{{ route('home') }}" class="flex flex-wrap items-end gap-2">
            <x-form-field
                name="period"
                label="Period"
                :value="$period"
                :options="['current_year' => 'Current year', 'last_year' => 'Last year']"
                class="min-w-[11rem]"
            />
            <x-ui.button type="submit">Apply</x-ui.button>
            @can('create', App\Models\Opportunity::class)
                <a href="{{ route('opportunities.create') }}" class="crm-btn-primary">Create deal</a>
            @endcan
        </form>
    </div>

    <div class="mb-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <x-ui.stat-card
            label="Open pipeline"
            :value="number_format($funnel['total'], 0)"
            hint="Sum of open opportunity amounts"
            tone="primary"
            icon="currency-dollar"
        />
        <x-ui.stat-card
            label="Potential revenue"
            :value="number_format($revenueBySource['total'], 0)"
            hint="By lead source this period"
            tone="navy"
            icon="chart-pie"
        />
        <x-ui.stat-card
            label="Tasks due today"
            :value="(string) $todayTasks->count()"
            hint="Your open tasks due today"
            tone="espresso"
            icon="list-check"
        />
        <x-ui.stat-card
            label="Events today"
            :value="(string) $todayEvents->count()"
            hint="On your calendar"
            tone="info"
            icon="calendar-event"
        />
    </div>

    <div class="grid gap-4 lg:grid-cols-12">
        <x-ui.card class="lg:col-span-7" aria-labelledby="funnel-heading">
            <div class="mb-3 flex flex-wrap items-end justify-between gap-2">
                <div>
                    <p class="text-[0.65rem] font-semibold uppercase tracking-wider text-muted">Pipeline</p>
                    <h2 id="funnel-heading" class="font-headline text-base font-semibold text-primary">Pipeline Funnel</h2>
                    <p class="text-sm text-muted">Open pipeline value: {{ number_format($funnel['total'], 2) }}</p>
                </div>
                <a href="{{ route('opportunities.index') }}" class="text-sm text-secondary no-underline">View opportunities</a>
            </div>
            @if (array_sum($funnel['values']) > 0)
                <x-chart
                    type="funnel"
                    :config="['labels' => $funnel['labels'], 'values' => $funnel['values'], 'label' => 'Pipeline']"
                    height="14rem"
                    aria-label="Pipeline funnel by stage"
                />
            @else
                <p class="text-sm text-muted">No open pipeline for this period.</p>
            @endif
        </x-ui.card>

        <x-ui.card class="lg:col-span-5" aria-labelledby="source-heading">
            <p class="text-[0.65rem] font-semibold uppercase tracking-wider text-muted">Sources</p>
            <h2 id="source-heading" class="mb-1 font-headline text-base font-semibold text-primary">Revenue by Source</h2>
            <p class="mb-3 text-sm text-muted">Potential revenue: {{ number_format($revenueBySource['total'], 2) }}</p>
            @if (array_sum($revenueBySource['values']) > 0)
                <x-chart
                    type="donut"
                    :config="['labels' => $revenueBySource['labels'], 'values' => $revenueBySource['values'], 'label' => 'Revenue']"
                    height="14rem"
                    aria-label="Potential revenue by lead source"
                />
            @else
                <p class="text-sm text-muted">No source data for this period.</p>
            @endif
        </x-ui.card>

        <x-ui.card class="lg:col-span-6" aria-labelledby="recent-heading">
            <h2 id="recent-heading" class="mb-3 font-headline text-base font-semibold text-primary">Recent Records</h2>
            @if ($recent->isEmpty())
                <x-ui.empty-state title="No recent records" description="Open a Lead, Account, Contact, Opportunity, or Case." />
            @else
                <ul class="divide-y divide-border text-sm">
                    @foreach ($recent as $row)
                        @php
                            $record = $row->viewable;
                            $url = match ($row->viewable_type) {
                                'lead' => route('leads.show', $record),
                                'account' => route('accounts.show', $record),
                                'contact' => route('contacts.show', $record),
                                'opportunity' => route('opportunities.show', $record),
                                'case' => route('cases.show', $record),
                                default => null,
                            };
                            $label = method_exists($record, 'displayName') ? $record->displayName() : class_basename($record);
                        @endphp
                        <li class="flex items-center justify-between gap-2 py-2.5">
                            @if ($url)
                                <a href="{{ $url }}" class="font-medium text-primary no-underline hover:underline">{{ $label }}</a>
                            @else
                                <span>{{ $label }}</span>
                            @endif
                            <x-ui.badge>{{ ucfirst($row->viewable_type) }}</x-ui.badge>
                        </li>
                    @endforeach
                </ul>
            @endif
        </x-ui.card>

        <x-ui.card class="lg:col-span-6" aria-labelledby="assistant-heading">
            <h2 id="assistant-heading" class="mb-3 font-headline text-base font-semibold text-primary">Assistant</h2>
            @if ($recommendations->isEmpty())
                <x-ui.empty-state title="No recommendations" description="Keep working your pipeline and activities." />
            @else
                <ul class="divide-y divide-border text-sm">
                    @foreach ($recommendations as $item)
                        <li class="flex flex-wrap items-start justify-between gap-3 py-3">
                            <div class="min-w-0 flex-1">
                                <a href="{{ $item['url'] }}" class="font-medium text-primary no-underline hover:underline">{{ $item['title'] }}</a>
                                <p class="text-muted">{{ $item['reason'] }}</p>
                                <a href="{{ $item['url'] }}" class="mt-1 inline-block text-secondary no-underline">{{ $item['action_label'] }}</a>
                            </div>
                            <form method="post" action="{{ route('home.assistant.dismiss') }}" class="shrink-0">
                                @csrf
                                <input type="hidden" name="recommendation_key" value="{{ $item['key'] }}">
                                <x-ui.button type="submit" variant="secondary" class="!min-h-9 !px-3 !text-xs">Dismiss</x-ui.button>
                            </form>
                        </li>
                    @endforeach
                </ul>
            @endif
        </x-ui.card>

        <x-ui.card class="lg:col-span-6" aria-labelledby="events-heading">
            <div class="mb-3 flex items-center justify-between gap-2">
                <h2 id="events-heading" class="font-headline text-base font-semibold text-primary">Today’s Events</h2>
                <a href="{{ route('calendar.index') }}" class="text-sm text-secondary no-underline">Calendar</a>
            </div>
            @if ($todayEvents->isEmpty())
                <p class="text-sm text-muted">No events scheduled for today.</p>
            @else
                <ul class="divide-y divide-border text-sm">
                    @foreach ($todayEvents as $event)
                        <li class="py-2.5">
                            <a href="{{ route('events.show', $event) }}" class="font-medium text-primary no-underline hover:underline">{{ $event->subject }}</a>
                            <p class="text-muted">
                                {{ $event->is_all_day ? 'All day' : $event->starts_at->format('g:i A').' – '.$event->ends_at->format('g:i A') }}
                                @if ($event->location)
                                    · {{ $event->location }}
                                @endif
                            </p>
                        </li>
                    @endforeach
                </ul>
            @endif
        </x-ui.card>

        <x-ui.card class="lg:col-span-6" aria-labelledby="tasks-heading">
            <div class="mb-3 flex items-center justify-between gap-2">
                <h2 id="tasks-heading" class="font-headline text-base font-semibold text-primary">Today’s Tasks</h2>
                <a href="{{ route('tasks.index', ['filter' => 'today']) }}" class="text-sm text-secondary no-underline">View tasks</a>
            </div>
            @if ($todayTasks->isEmpty())
                <p class="text-sm text-muted">No tasks due today.</p>
            @else
                <ul class="divide-y divide-border text-sm">
                    @foreach ($todayTasks as $task)
                        <li class="py-2.5">
                            <a href="{{ route('tasks.show', $task) }}" class="font-medium text-primary no-underline hover:underline">{{ $task->subject }}</a>
                            <p class="text-muted">
                                {{ $task->due_date?->toDateString() ?? '—' }}
                                @if ($task->related)
                                    · {{ ucfirst($task->related_type) }}
                                @endif
                            </p>
                        </li>
                    @endforeach
                </ul>
            @endif
        </x-ui.card>

        <x-ui.card class="lg:col-span-12" aria-labelledby="deals-heading">
            <div class="mb-3 flex items-center justify-between gap-2">
                <h2 id="deals-heading" class="font-headline text-base font-semibold text-primary">Key Deals</h2>
                <a href="{{ route('opportunities.index') }}" class="text-sm text-secondary no-underline">View all</a>
            </div>
            @if ($keyDeals->isEmpty())
                <p class="text-sm text-muted">No high-probability or large-amount deals yet.</p>
            @else
                <ul class="divide-y divide-border text-sm sm:columns-2 sm:gap-8">
                    @foreach ($keyDeals as $deal)
                        <li class="break-inside-avoid py-2.5">
                            <a href="{{ route('opportunities.show', $deal) }}" class="font-medium text-primary no-underline hover:underline">{{ $deal->name }}</a>
                            <p class="text-muted">
                                {{ $deal->account?->name ?? '—' }} ·
                                <span class="tabular-metrics text-secondary">{{ $deal->amount !== null ? number_format((float) $deal->amount, 2) : '—' }}</span> ·
                                {{ $deal->close_date?->toDateString() ?? '—' }} ·
                                {{ \App\Support\PicklistOptions::options('opportunity_stage')[$deal->stage] ?? $deal->stage }}
                            </p>
                        </li>
                    @endforeach
                </ul>
            @endif
        </x-ui.card>
    </div>
@endsection
