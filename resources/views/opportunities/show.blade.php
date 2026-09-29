@extends('layouts.app')

@section('title', $opportunity->displayName().' — '.config('app.name'))

@section('content')
    <x-breadcrumbs :items="[
        ['label' => 'Opportunities', 'url' => route('opportunities.index')],
        ['label' => $opportunity->displayName()],
    ]" />

    <div class="mb-4 flex flex-wrap items-start justify-between gap-3">
        <div>
            <h1>{{ $opportunity->displayName() }}</h1>
            <p class="text-sm text-text/70">
                {{ $opportunity->account?->name ?? '—' }} · Stage: {{ $stageLabel }} · Owner: {{ $opportunity->owner?->name ?? '—' }}
                @if ($opportunity->isArchived())
                    · <span class="font-medium text-text/80">Archived</span>
                @endif
            </p>
        </div>
        <div class="flex flex-wrap gap-2">
            @can('update', $opportunity)
                <a href="{{ route('opportunities.edit', $opportunity) }}" class="inline-flex min-h-11 items-center rounded bg-secondary px-4 py-2 text-sm font-semibold text-white no-underline">Edit</a>
            @endcan
            @can('clone', $opportunity)
                <a href="{{ route('opportunities.clone', $opportunity) }}" class="inline-flex min-h-11 items-center rounded border border-secondary px-4 py-2 text-sm font-semibold text-secondary no-underline">Clone</a>
            @endcan
            @can('archive', $opportunity)
                <form method="post" action="{{ route('opportunities.archive', $opportunity) }}" onsubmit="return confirm('Archive this opportunity? It will be hidden from the default list.');">
                    @csrf
                    <button type="submit" class="inline-flex min-h-11 items-center rounded border border-black/20 px-4 py-2 text-sm">Archive</button>
                </form>
            @endcan
            @can('delete', $opportunity)
                <form method="post" action="{{ route('opportunities.destroy', $opportunity) }}" onsubmit="return confirm('Archive this opportunity?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="inline-flex min-h-11 items-center rounded border border-error px-4 py-2 text-sm font-semibold text-error">Delete</button>
                </form>
            @endcan
        </div>
    </div>

    <div class="mb-6 grid gap-4 sm:grid-cols-3">
        <section class="rounded bg-card p-4 shadow-[var(--shadow-card)]">
            <h2 class="text-xs font-semibold uppercase tracking-wide text-text/60">Amount</h2>
            <p class="mt-1 text-2xl font-semibold text-primary">{{ $opportunity->amount !== null ? number_format((float) $opportunity->amount, 2) : '—' }}</p>
        </section>
        <section class="rounded bg-card p-4 shadow-[var(--shadow-card)]">
            <h2 class="text-xs font-semibold uppercase tracking-wide text-text/60">Probability</h2>
            <p class="mt-1 text-2xl font-semibold text-primary">{{ $opportunity->probability }}%</p>
        </section>
        <section class="rounded bg-card p-4 shadow-[var(--shadow-card)]">
            <h2 class="text-xs font-semibold uppercase tracking-wide text-text/60">Expected Revenue</h2>
            <p class="mt-1 text-2xl font-semibold text-primary">{{ $opportunity->expected_revenue !== null ? number_format((float) $opportunity->expected_revenue, 2) : '—' }}</p>
        </section>
    </div>

    <section class="mb-6 rounded bg-card p-4 shadow-[var(--shadow-card)]">
        <h2 class="mb-3 text-base font-semibold text-primary">Stage Path</h2>
        <x-stage-path
            :stages="$stages"
            :stage-values="$stageValues"
            :current-stage="$opportunity->stage"
            :action="auth()->user()->can('updateStage', $opportunity) ? route('opportunities.change-stage', $opportunity) : null"
            :disabled="! auth()->user()->can('updateStage', $opportunity)"
        />
    </section>

    @can('changeOwner', $opportunity)
        <form method="post" action="{{ route('opportunities.change-owner', $opportunity) }}" class="mb-6 flex flex-wrap items-end gap-2 rounded bg-card p-4 shadow-[var(--shadow-card)]">
            @csrf
            <x-form-field name="owner_id" label="Change Owner" :value="$opportunity->owner_id" :options="$owners->mapWithKeys(fn ($u) => [$u->id => $u->name])->all()" class="min-w-[14rem]" required />
            <label class="flex min-h-11 items-center gap-2 text-sm">
                <input type="checkbox" name="notify_new_owner" value="1" class="size-4 rounded border-black/20">
                Notify new owner (stub)
            </label>
            <label class="flex min-h-11 items-center gap-2 text-sm">
                <input type="checkbox" name="transfer_open_activities" value="1" class="size-4 rounded border-black/20">
                Transfer open activities (stub)
            </label>
            <button type="submit" class="inline-flex min-h-11 items-center rounded border border-black/20 px-4 py-2 text-sm">Change Owner</button>
        </form>
    @endcan

    <div class="grid gap-4 lg:grid-cols-2">
        <section class="rounded bg-card p-4 shadow-[var(--shadow-card)]">
            <h2 class="mb-3 text-base font-semibold text-primary">Opportunity Details</h2>
            <dl class="grid gap-2 text-sm sm:grid-cols-2">
                <div><dt class="text-text/60">Account</dt><dd>@if ($opportunity->account)<a href="{{ route('accounts.show', $opportunity->account) }}">{{ $opportunity->account->name }}</a>@else — @endif</dd></div>
                <div><dt class="text-text/60">Type</dt><dd>{{ $typeLabel ?: '—' }}</dd></div>
                <div><dt class="text-text/60">Lead Source</dt><dd>{{ $sourceLabel ?: '—' }}</dd></div>
                <div><dt class="text-text/60">Close Date</dt><dd>{{ $opportunity->close_date?->toDateString() ?? '—' }}</dd></div>
                <div><dt class="text-text/60">Next Step</dt><dd>{{ $opportunity->next_step ?: '—' }}</dd></div>
                <div><dt class="text-text/60">Closed</dt><dd>{{ $opportunity->is_closed ? ($opportunity->is_won ? 'Won' : 'Lost') : 'Open' }}</dd></div>
            </dl>
        </section>

        <section class="rounded bg-card p-4 shadow-[var(--shadow-card)]">
            <h2 class="mb-3 text-base font-semibold text-primary">Description</h2>
            <p class="text-sm whitespace-pre-wrap">{{ $opportunity->description ?: '—' }}</p>
        </section>

        <section class="rounded bg-card p-4 shadow-[var(--shadow-card)] lg:col-span-2">
            <h2 class="mb-3 text-base font-semibold text-primary">System Information</h2>
            <dl class="grid gap-2 text-sm sm:grid-cols-2">
                <div><dt class="text-text/60">Owner</dt><dd>{{ $opportunity->owner?->name ?? '—' }}</dd></div>
                <div><dt class="text-text/60">Created By</dt><dd>{{ $opportunity->creator?->name ?? '—' }}</dd></div>
                <div><dt class="text-text/60">Created Date</dt><dd>{{ $opportunity->created_at?->toDateTimeString() ?? '—' }}</dd></div>
                <div><dt class="text-text/60">Last Modified By</dt><dd>{{ $opportunity->updater?->name ?? '—' }}</dd></div>
                <div><dt class="text-text/60">Last Modified Date</dt><dd>{{ $opportunity->updated_at?->toDateTimeString() ?? '—' }}</dd></div>
            </dl>
        </section>
    </div>

    <div class="mt-6 grid gap-4">
        <x-related-list title="Products" empty="No products linked to this opportunity." />
        <x-related-list title="Quotes" empty="No quotes for this opportunity." />
        <x-related-list title="Activity History" empty="No activity history yet." />
        <x-related-list title="Open Activities" empty="No open tasks.">
            @if (($openTasks ?? collect())->isNotEmpty())
                <ul class="divide-y divide-black/10 text-sm">
                    @foreach ($openTasks as $task)
                        <li class="flex flex-wrap items-center justify-between gap-2 py-2">
                            <a href="{{ route('tasks.show', $task) }}">{{ $task->subject }}</a>
                            <span class="text-text/60">{{ $task->due_date?->toDateString() ?? 'No due date' }}</span>
                        </li>
                    @endforeach
                </ul>
            @endif
            <p class="mt-2 text-xs text-text/60">
                <a href="{{ route('tasks.create', ['related_type' => 'opportunity', 'related_id' => $opportunity->id]) }}">New Task</a>
                · Events arrive with Dev B calendar
            </p>
        </x-related-list>

        <x-related-list title="Stage History" :empty="$opportunity->stageHistories->isEmpty() ? 'No stage changes recorded.' : null">
            @if ($opportunity->stageHistories->isNotEmpty())
                <ul class="divide-y divide-black/10 text-sm">
                    @foreach ($opportunity->stageHistories as $history)
                        <li class="py-2">
                            <span class="font-medium">{{ $stages[$history->to_stage] ?? $history->to_stage }}</span>
                            @if ($history->from_stage)
                                <span class="text-text/60">from {{ $stages[$history->from_stage] ?? $history->from_stage }}</span>
                            @endif
                            · {{ $history->probability }}%
                            · {{ $history->created_at?->toDateTimeString() }}
                            @if ($history->changedByUser)
                                · {{ $history->changedByUser->name }}
                            @endif
                        </li>
                    @endforeach
                </ul>
            @endif
        </x-related-list>

        <x-attachments-related-list :attachable="$opportunity" attachable-type="opportunity" />
    </div>
@endsection
