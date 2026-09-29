@extends('layouts.app')

@section('title', $lead->displayName().' — '.config('app.name'))

@section('content')
    <div class="mb-4 flex flex-wrap items-start justify-between gap-3">
        <div>
            <h1>{{ trim(($salutationLabel ? $salutationLabel.' ' : '').$lead->displayName()) }}</h1>
            <p class="text-sm text-text/70">{{ $lead->company }} · Status: {{ $statusLabel }} · Owner: {{ $lead->owner?->name ?? '—' }}</p>
        </div>
        <div class="flex flex-wrap gap-2">
            @can('update', $lead)
                <a href="{{ route('leads.edit', $lead) }}" class="inline-flex min-h-11 items-center rounded bg-secondary px-4 py-2 text-sm font-semibold text-white no-underline">Edit</a>
            @endcan
            @can('delete', $lead)
                <form method="post" action="{{ route('leads.destroy', $lead) }}" onsubmit="return confirm('Delete this lead?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="inline-flex min-h-11 items-center rounded border border-error px-4 py-2 text-sm font-semibold text-error">Delete</button>
                </form>
            @endcan
            @can('convert', $lead)
                <a href="{{ route('leads.convert', $lead) }}" class="inline-flex min-h-11 items-center rounded bg-primary px-4 py-2 text-sm font-semibold text-white no-underline">Convert</a>
            @elseif ($lead->isReadOnlyConverted())
                <span class="inline-flex min-h-11 items-center rounded border border-black/20 px-4 py-2 text-sm text-text/70">Converted</span>
            @endcan
        </div>
    </div>

    <div class="mb-6 flex flex-wrap gap-4">
        @can('changeOwner', $lead)
            <form method="post" action="{{ route('leads.change-owner', $lead) }}" class="flex flex-wrap items-end gap-2 rounded bg-card p-4 shadow-[var(--shadow-card)]">
                @csrf
                <x-form-field name="owner_id" label="Change Owner" :value="$lead->owner_id" :options="$owners->mapWithKeys(fn ($u) => [$u->id => $u->name])->all()" class="min-w-[14rem]" required />
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

        @can('changeStatus', $lead)
            <form method="post" action="{{ route('leads.change-status', $lead) }}" class="flex flex-wrap items-end gap-2 rounded bg-card p-4 shadow-[var(--shadow-card)]">
                @csrf
                <x-form-field name="status" label="Change Status" :value="$lead->status?->value" :options="$statuses" class="min-w-[14rem]" required />
                <button type="submit" class="inline-flex min-h-11 items-center rounded border border-black/20 px-4 py-2 text-sm">Update Status</button>
            </form>
        @endcan
    </div>

    <div class="grid gap-4 lg:grid-cols-2">
        <section class="rounded bg-card p-4 shadow-[var(--shadow-card)]">
            <h2 class="mb-3 text-base font-semibold text-primary">Lead Details</h2>
            <dl class="grid gap-2 text-sm sm:grid-cols-2">
                <div><dt class="text-text/60">Company</dt><dd>{{ $lead->company }}</dd></div>
                <div><dt class="text-text/60">Title</dt><dd>{{ $lead->title ?: '—' }}</dd></div>
                <div><dt class="text-text/60">Status</dt><dd>{{ $statusLabel }}</dd></div>
                <div><dt class="text-text/60">Lead Source</dt><dd>{{ $sourceLabel ?: '—' }}</dd></div>
                <div><dt class="text-text/60">Rating</dt><dd>{{ $ratingLabel ?: '—' }}</dd></div>
                <div><dt class="text-text/60">Industry</dt><dd>{{ $industryLabel ?: '—' }}</dd></div>
                <div><dt class="text-text/60">Email</dt><dd>{{ $lead->email ?: '—' }}</dd></div>
                <div><dt class="text-text/60">Phone</dt><dd>{{ $lead->phone ?: '—' }}</dd></div>
                <div><dt class="text-text/60">Mobile</dt><dd>{{ $lead->mobile ?: '—' }}</dd></div>
                <div><dt class="text-text/60">Website</dt><dd>{{ $lead->website ?: '—' }}</dd></div>
                <div><dt class="text-text/60">Annual Revenue</dt><dd>{{ $lead->annual_revenue ?? '—' }}</dd></div>
                <div><dt class="text-text/60">Employees</dt><dd>{{ $lead->number_of_employees ?? '—' }}</dd></div>
            </dl>
        </section>

        <section class="rounded bg-card p-4 shadow-[var(--shadow-card)]">
            <h2 class="mb-3 text-base font-semibold text-primary">Address Information</h2>
            <p class="text-sm">{{ $lead->street ?: '—' }}</p>
            <p class="text-sm">{{ collect([$lead->city, $lead->state, $lead->postal_code])->filter()->implode(', ') ?: '—' }}</p>
            <p class="text-sm">{{ $lead->country ?: '—' }}</p>
        </section>

        <section class="rounded bg-card p-4 shadow-[var(--shadow-card)]">
            <h2 class="mb-3 text-base font-semibold text-primary">Additional Information</h2>
            <p class="text-sm whitespace-pre-wrap">{{ $lead->description ?: '—' }}</p>
        </section>

        <section class="rounded bg-card p-4 shadow-[var(--shadow-card)]">
            <h2 class="mb-3 text-base font-semibold text-primary">System Information</h2>
            <dl class="grid gap-2 text-sm sm:grid-cols-2">
                <div><dt class="text-text/60">Owner</dt><dd>{{ $lead->owner?->name ?? '—' }}</dd></div>
                <div><dt class="text-text/60">Created By</dt><dd>{{ $lead->creator?->name ?? '—' }}</dd></div>
                <div><dt class="text-text/60">Created Date</dt><dd>{{ $lead->created_at?->toDateTimeString() ?? '—' }}</dd></div>
                <div><dt class="text-text/60">Last Modified By</dt><dd>{{ $lead->updater?->name ?? '—' }}</dd></div>
                <div><dt class="text-text/60">Last Modified Date</dt><dd>{{ $lead->updated_at?->toDateTimeString() ?? '—' }}</dd></div>
            </dl>
        </section>
    </div>
@endsection
