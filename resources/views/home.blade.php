@extends('layouts.app')

@section('title', 'Home — '.config('app.name'))

@section('content')
    <div class="mb-4 flex flex-wrap items-end justify-between gap-3">
        <div>
            <h1>Home</h1>
            <p class="text-sm text-text/70">
                Signed in as <strong>{{ auth()->user()->name }}</strong>
                @if (auth()->user()->role)
                    ({{ auth()->user()->role->name }})
                @endif
            </p>
        </div>
        <form method="get" action="{{ route('home') }}" class="flex items-end gap-2">
            <x-form-field
                name="period"
                label="Period"
                :value="$period"
                :options="['current_year' => 'Current year', 'last_year' => 'Last year']"
                class="min-w-[12rem]"
            />
            <button type="submit" class="inline-flex min-h-11 items-center rounded border border-black/20 bg-card px-4 py-2 text-sm">Apply</button>
        </form>
    </div>

    <div class="grid gap-4 lg:grid-cols-2">
        <section class="rounded bg-card p-4 shadow-[var(--shadow-card)]" aria-labelledby="recent-heading">
            <h2 id="recent-heading" class="mb-3 text-base font-semibold text-primary">Recent Records</h2>
            @if ($recent->isEmpty())
                <p class="text-sm text-text/70">No recent records yet. Open a Lead, Account, Contact, Opportunity, or Case.</p>
            @else
                <ul class="divide-y divide-black/10 text-sm">
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
                        <li class="flex items-center justify-between gap-2 py-2">
                            @if ($url)
                                <a href="{{ $url }}">{{ $label }}</a>
                            @else
                                <span>{{ $label }}</span>
                            @endif
                            <span class="text-text/60">{{ ucfirst($row->viewable_type) }}</span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>

        <section class="rounded bg-card p-4 shadow-[var(--shadow-card)]" aria-labelledby="assistant-heading">
            <h2 id="assistant-heading" class="mb-3 text-base font-semibold text-primary">Assistant</h2>
            <p class="text-sm text-text/70">Rule-based recommendations arrive in Phase 5 (FR-HOME-007).</p>
        </section>

        <section class="rounded bg-card p-4 shadow-[var(--shadow-card)] lg:col-span-2" aria-labelledby="funnel-heading">
            <div class="mb-3 flex flex-wrap items-end justify-between gap-2">
                <div>
                    <h2 id="funnel-heading" class="text-base font-semibold text-primary">Pipeline Funnel</h2>
                    <p class="text-sm text-text/70">Open pipeline value: {{ number_format($funnel['total'], 2) }}</p>
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
                <p class="text-sm text-text/70">No open pipeline for this period.</p>
            @endif
        </section>

        <section class="rounded bg-card p-4 shadow-[var(--shadow-card)]" aria-labelledby="source-heading">
            <h2 id="source-heading" class="mb-1 text-base font-semibold text-primary">Revenue by Source</h2>
            <p class="mb-3 text-sm text-text/70">Potential revenue: {{ number_format($revenueBySource['total'], 2) }}</p>
            @if (array_sum($revenueBySource['values']) > 0)
                <x-chart
                    type="donut"
                    :config="['labels' => $revenueBySource['labels'], 'values' => $revenueBySource['values'], 'label' => 'Revenue']"
                    height="14rem"
                    aria-label="Potential revenue by lead source"
                />
            @else
                <p class="text-sm text-text/70">No source data for this period.</p>
            @endif
        </section>

        <section class="rounded bg-card p-4 shadow-[var(--shadow-card)]" aria-labelledby="deals-heading">
            <div class="mb-3 flex items-center justify-between gap-2">
                <h2 id="deals-heading" class="text-base font-semibold text-primary">Key Deals</h2>
                <a href="{{ route('opportunities.index') }}" class="text-sm text-secondary no-underline">View all</a>
            </div>
            @if ($keyDeals->isEmpty())
                <p class="text-sm text-text/70">No high-probability or large-amount deals yet.</p>
            @else
                <ul class="divide-y divide-black/10 text-sm">
                    @foreach ($keyDeals as $deal)
                        <li class="py-2">
                            <a href="{{ route('opportunities.show', $deal) }}" class="font-medium">{{ $deal->name }}</a>
                            <p class="text-text/60">
                                {{ $deal->account?->name ?? '—' }} ·
                                {{ $deal->amount !== null ? number_format((float) $deal->amount, 2) : '—' }} ·
                                {{ $deal->close_date?->toDateString() ?? '—' }} ·
                                {{ \App\Support\PicklistOptions::options('opportunity_stage')[$deal->stage] ?? $deal->stage }}
                            </p>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>
    </div>
@endsection
