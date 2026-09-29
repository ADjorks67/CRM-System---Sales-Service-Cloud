@extends('layouts.app')

@section('title', $crmCase->displayName().' — '.config('app.name'))

@section('content')
    @php
        $priorityClass = match ($crmCase->priority) {
            'high' => 'text-error',
            'medium' => 'text-warning',
            'low' => 'text-success',
            default => '',
        };
    @endphp

    <div class="mb-4 flex flex-wrap items-start justify-between gap-3">
        <div>
            <h1>{{ $crmCase->displayName() }}</h1>
            <p class="text-sm text-text/70">
                {{ $crmCase->case_number }}
                · Status: {{ $statusLabel }}
                · Priority: <span @class([$priorityClass])>{{ $priorityLabel }}</span>
                · Owner: {{ $crmCase->owner?->name ?? '—' }}
            </p>
        </div>
        <div class="flex flex-wrap gap-2">
            @can('update', $crmCase)
                <a href="{{ route('cases.edit', $crmCase) }}" class="inline-flex min-h-11 items-center rounded bg-secondary px-4 py-2 text-sm font-semibold text-white no-underline">Edit</a>
            @endcan
            @can('delete', $crmCase)
                <form method="post" action="{{ route('cases.destroy', $crmCase) }}" onsubmit="return confirm('Delete this case?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="inline-flex min-h-11 items-center rounded border border-error px-4 py-2 text-sm font-semibold text-error">Delete</button>
                </form>
            @endcan
            @can('reopen', $crmCase)
                <form method="post" action="{{ route('cases.reopen', $crmCase) }}" onsubmit="return confirm('Reopen this case?');">
                    @csrf
                    <button type="submit" class="inline-flex min-h-11 items-center rounded border border-secondary px-4 py-2 text-sm font-semibold text-secondary">Reopen</button>
                </form>
            @endcan
        </div>
    </div>

    <div class="mb-6 flex flex-wrap gap-4">
        @can('changeOwner', $crmCase)
            <form method="post" action="{{ route('cases.change-owner', $crmCase) }}" class="flex flex-wrap items-end gap-2 rounded bg-card p-4 shadow-[var(--shadow-card)]">
                @csrf
                <x-form-field name="owner_id" label="Change Owner" :value="$crmCase->owner_id" :options="$owners->mapWithKeys(fn ($u) => [$u->id => $u->name])->all()" class="min-w-[14rem]" required />
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

        @can('changeStatus', $crmCase)
            <form method="post" action="{{ route('cases.change-status', $crmCase) }}" class="flex flex-wrap items-end gap-2 rounded bg-card p-4 shadow-[var(--shadow-card)]">
                @csrf
                <x-form-field name="status" label="Change Status" :value="$crmCase->status" :options="$statuses" class="min-w-[14rem]" required />
                <button type="submit" class="inline-flex min-h-11 items-center rounded border border-black/20 px-4 py-2 text-sm">Update Status</button>
            </form>
        @endcan
    </div>

    <div class="grid gap-4 lg:grid-cols-2">
        <section class="rounded bg-card p-4 shadow-[var(--shadow-card)]">
            <h2 class="mb-3 text-base font-semibold text-primary">Case Details</h2>
            <dl class="grid gap-2 text-sm sm:grid-cols-2">
                <div><dt class="text-text/60">Case Number</dt><dd>{{ $crmCase->case_number }}</dd></div>
                <div><dt class="text-text/60">Subject</dt><dd>{{ $crmCase->subject ?: '—' }}</dd></div>
                <div><dt class="text-text/60">Status</dt><dd>{{ $statusLabel }}</dd></div>
                <div><dt class="text-text/60">Priority</dt><dd><span @class([$priorityClass])>{{ $priorityLabel }}</span></dd></div>
                <div><dt class="text-text/60">Origin</dt><dd>{{ $originLabel ?: '—' }}</dd></div>
                <div><dt class="text-text/60">Type</dt><dd>{{ $typeLabel ?: '—' }}</dd></div>
                <div><dt class="text-text/60">Reason</dt><dd>{{ $reasonLabel ?: '—' }}</dd></div>
                <div><dt class="text-text/60">Closed Date</dt><dd>{{ $crmCase->closed_at?->toDateTimeString() ?? '—' }}</dd></div>
                <div><dt class="text-text/60">Account</dt><dd>
                    @if ($crmCase->account)
                        <a href="{{ route('accounts.show', $crmCase->account) }}">{{ $crmCase->account->name }}</a>
                    @else
                        —
                    @endif
                </dd></div>
                <div><dt class="text-text/60">Contact</dt><dd>
                    @if ($crmCase->contact)
                        <a href="{{ route('contacts.show', $crmCase->contact) }}">{{ $crmCase->contact->displayName() }}</a>
                    @else
                        —
                    @endif
                </dd></div>
            </dl>
        </section>

        <section class="rounded bg-card p-4 shadow-[var(--shadow-card)]">
            <h2 class="mb-3 text-base font-semibold text-primary">Web Information</h2>
            <dl class="grid gap-2 text-sm sm:grid-cols-2">
                <div><dt class="text-text/60">Web Name</dt><dd>{{ $crmCase->web_name ?: '—' }}</dd></div>
                <div><dt class="text-text/60">Web Email</dt><dd>{{ $crmCase->web_email ?: '—' }}</dd></div>
                <div><dt class="text-text/60">Web Company</dt><dd>{{ $crmCase->web_company ?: '—' }}</dd></div>
                <div><dt class="text-text/60">Web Phone</dt><dd>{{ $crmCase->web_phone ?: '—' }}</dd></div>
            </dl>
        </section>

        <section class="rounded bg-card p-4 shadow-[var(--shadow-card)]">
            <h2 class="mb-3 text-base font-semibold text-primary">Description</h2>
            <p class="text-sm whitespace-pre-wrap">{{ $crmCase->description ?: '—' }}</p>
        </section>

        <section class="rounded bg-card p-4 shadow-[var(--shadow-card)]">
            <h2 class="mb-3 text-base font-semibold text-primary">Internal Comments</h2>
            <p class="text-sm whitespace-pre-wrap">{{ $crmCase->internal_comments ?: '—' }}</p>
        </section>

        <section class="rounded bg-card p-4 shadow-[var(--shadow-card)] lg:col-span-2">
            <h2 class="mb-3 text-base font-semibold text-primary">System Information</h2>
            <dl class="grid gap-2 text-sm sm:grid-cols-2">
                <div><dt class="text-text/60">Owner</dt><dd>{{ $crmCase->owner?->name ?? '—' }}</dd></div>
                <div><dt class="text-text/60">Created By</dt><dd>{{ $crmCase->creator?->name ?? '—' }}</dd></div>
                <div><dt class="text-text/60">Created Date</dt><dd>{{ $crmCase->created_at?->toDateTimeString() ?? '—' }}</dd></div>
                <div><dt class="text-text/60">Last Modified By</dt><dd>{{ $crmCase->updater?->name ?? '—' }}</dd></div>
                <div><dt class="text-text/60">Last Modified Date</dt><dd>{{ $crmCase->updated_at?->toDateTimeString() ?? '—' }}</dd></div>
            </dl>
        </section>
    </div>

    <div class="mt-6 grid gap-4 lg:grid-cols-3">
        <x-related-list title="Emails" empty="No emails yet." />
        <x-related-list title="Activity History" empty="No activity history yet." />
        <x-related-list title="Attachments" empty="No attachments yet." />
    </div>
@endsection
