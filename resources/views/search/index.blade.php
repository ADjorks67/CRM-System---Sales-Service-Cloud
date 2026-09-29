@extends('layouts.app')

@section('title', 'Search — '.config('app.name'))

@section('content')
    <div class="mb-4">
        <h1>Search</h1>
        <p class="text-sm text-text/70">Global search across Leads, Accounts, Contacts, Opportunities, and Cases (FR-SRCH-001).</p>
    </div>

    <form method="get" action="{{ route('search.index') }}" class="mb-6 flex flex-wrap gap-2 rounded bg-card p-4 shadow-[var(--shadow-card)]">
        <label for="search-q" class="sr-only">Search query</label>
        <input id="search-q" type="search" name="q" value="{{ $q }}" minlength="2" required placeholder="Search…" class="min-h-11 min-w-[14rem] flex-1 rounded border border-black/20 bg-page px-3 py-2 text-sm">
        <x-form-field name="type" label="Object" :value="$type ?? ''" :options="['' => 'All objects'] + $objectLabels" class="min-w-[12rem]" />
        <button type="submit" class="inline-flex min-h-11 items-center rounded bg-secondary px-4 py-2 text-sm font-semibold text-white">Search</button>
    </form>

    @if ($recentQueries->isNotEmpty())
        <section class="mb-6" aria-label="Recent searches">
            <h2 class="mb-2 text-sm font-semibold text-primary">Recent searches</h2>
            <ul class="flex flex-wrap gap-2 text-sm">
                @foreach ($recentQueries as $recent)
                    <li>
                        <a href="{{ route('search.index', ['q' => $recent]) }}" class="rounded border border-black/10 bg-card px-3 py-1 no-underline hover:bg-secondary/5">{{ $recent }}</a>
                    </li>
                @endforeach
            </ul>
        </section>
    @endif

    @if (mb_strlen($q) < 2)
        <p class="text-sm text-text/70">Enter at least 2 characters to search.</p>
    @elseif ($results->isEmpty())
        <p class="text-sm text-text/70">No matches for “{{ $q }}”.</p>
    @else
        <p class="mb-3 text-sm text-text/70">{{ $results->count() }} result(s)</p>
        <ul class="divide-y divide-black/10 rounded bg-card shadow-[var(--shadow-card)]">
            @foreach ($results as $record)
                @php
                    $typeKey = match (true) {
                        $record instanceof \App\Models\Lead => 'leads',
                        $record instanceof \App\Models\Account => 'accounts',
                        $record instanceof \App\Models\Contact => 'contacts',
                        $record instanceof \App\Models\Opportunity => 'opportunities',
                        $record instanceof \App\Models\CrmCase => 'cases',
                        default => 'records',
                    };
                    $url = match ($typeKey) {
                        'leads' => route('leads.show', $record),
                        'accounts' => route('accounts.show', $record),
                        'contacts' => route('contacts.show', $record),
                        'opportunities' => route('opportunities.show', $record),
                        'cases' => route('cases.show', $record),
                        default => '#',
                    };
                    $label = method_exists($record, 'displayName') ? $record->displayName() : (string) $record->getKey();
                @endphp
                <li class="flex flex-wrap items-center justify-between gap-2 px-4 py-3 text-sm">
                    <div>
                        <a href="{{ $url }}" class="font-medium">{{ $label }}</a>
                        <p class="text-text/60">{{ $objectLabels[$typeKey] ?? ucfirst($typeKey) }}</p>
                    </div>
                    <a href="{{ $url }}" class="text-secondary no-underline">Open</a>
                </li>
            @endforeach
        </ul>
    @endif
@endsection
