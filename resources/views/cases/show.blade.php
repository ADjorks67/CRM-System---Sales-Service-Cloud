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

    <x-breadcrumbs :items="[
        ['label' => 'Cases', 'url' => route('cases.index')],
        ['label' => $crmCase->displayName()],
    ]" />

    <div class="mb-4 flex flex-wrap items-start justify-between gap-3">
        <div>
            <x-ui.back :href="route('cases.index')" label="Back to Cases" class="mb-2" />
            <h1>{{ $crmCase->displayName() }}</h1>
            <p class="text-sm text-muted">
                {{ $crmCase->case_number }}
                · Status: {{ $statusLabel }}
                · Priority: <span @class([$priorityClass])>{{ $priorityLabel }}</span>
                · Owner: {{ $crmCase->owner?->name ?? '—' }}
            </p>
        </div>
        <div class="flex flex-wrap gap-2">
            @can('update', $crmCase)
                <a href="{{ route('cases.edit', $crmCase) }}" class="crm-btn-secondary">Edit</a>
            @endcan
            @can('delete', $crmCase)
                <form method="post" action="{{ route('cases.destroy', $crmCase) }}" onsubmit="return confirm('Delete this case?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="crm-btn-danger">Delete</button>
                </form>
            @endcan
            @can('reopen', $crmCase)
                <form method="post" action="{{ route('cases.reopen', $crmCase) }}" onsubmit="return confirm('Reopen this case?');">
                    @csrf
                    <button type="submit" class="crm-btn-secondary">Reopen</button>
                </form>
            @endcan
        </div>
    </div>

    <div class="mb-6 flex flex-wrap gap-4">
        @can('changeOwner', $crmCase)
            <form method="post" action="{{ route('cases.change-owner', $crmCase) }}" class="flex flex-wrap items-end gap-2 crm-card p-4 sm:p-5">
                @csrf
                <x-form-field name="owner_id" label="Change Owner" :value="$crmCase->owner_id" :options="$owners->mapWithKeys(fn ($u) => [$u->id => $u->name])->all()" class="min-w-[14rem]" required />
                <label class="flex min-h-11 items-center gap-2 text-sm">
                    <input type="checkbox" name="notify_new_owner" value="1" class="size-4 rounded border-border">
                    Notify new owner (stub)
                </label>
                <label class="flex min-h-11 items-center gap-2 text-sm">
                    <input type="checkbox" name="transfer_open_activities" value="1" class="size-4 rounded border-border">
                    Transfer open activities (stub)
                </label>
                <button type="submit" class="crm-btn-secondary">Change Owner</button>
            </form>
        @endcan

        @can('changeStatus', $crmCase)
            <form method="post" action="{{ route('cases.change-status', $crmCase) }}" class="flex flex-wrap items-end gap-2 crm-card p-4 sm:p-5">
                @csrf
                <x-form-field name="status" label="Change Status" :value="$crmCase->status" :options="$statuses" class="min-w-[14rem]" required />
                <button type="submit" class="crm-btn-secondary">Update Status</button>
            </form>
        @endcan
    </div>

    <div class="grid gap-4 lg:grid-cols-2">
        <section class="crm-card p-4 sm:p-5">
            <h2 class="mb-3 text-base font-semibold text-primary">Case Details</h2>
            <dl class="grid gap-2 text-sm sm:grid-cols-2">
                <div><dt class="text-muted">Case Number</dt><dd>{{ $crmCase->case_number }}</dd></div>
                <div><dt class="text-muted">Subject</dt><dd>{{ $crmCase->subject ?: '—' }}</dd></div>
                <div><dt class="text-muted">Status</dt><dd>{{ $statusLabel }}</dd></div>
                <div><dt class="text-muted">Priority</dt><dd><span @class([$priorityClass])>{{ $priorityLabel }}</span></dd></div>
                <div><dt class="text-muted">Origin</dt><dd>{{ $originLabel ?: '—' }}</dd></div>
                <div><dt class="text-muted">Type</dt><dd>{{ $typeLabel ?: '—' }}</dd></div>
                <div><dt class="text-muted">Reason</dt><dd>{{ $reasonLabel ?: '—' }}</dd></div>
                <div><dt class="text-muted">Closed Date</dt><dd>{{ $crmCase->closed_at?->toDateTimeString() ?? '—' }}</dd></div>
                <div><dt class="text-muted">Account</dt><dd>
                    @if ($crmCase->account)
                        <a href="{{ route('accounts.show', $crmCase->account) }}">{{ $crmCase->account->name }}</a>
                    @else
                        —
                    @endif
                </dd></div>
                <div><dt class="text-muted">Contact</dt><dd>
                    @if ($crmCase->contact)
                        <a href="{{ route('contacts.show', $crmCase->contact) }}">{{ $crmCase->contact->displayName() }}</a>
                    @else
                        —
                    @endif
                </dd></div>
            </dl>
        </section>

        <section class="crm-card p-4 sm:p-5">
            <h2 class="mb-3 text-base font-semibold text-primary">Web Information</h2>
            <dl class="grid gap-2 text-sm sm:grid-cols-2">
                <div><dt class="text-muted">Web Name</dt><dd>{{ $crmCase->web_name ?: '—' }}</dd></div>
                <div><dt class="text-muted">Web Email</dt><dd>{{ $crmCase->web_email ?: '—' }}</dd></div>
                <div><dt class="text-muted">Web Company</dt><dd>{{ $crmCase->web_company ?: '—' }}</dd></div>
                <div><dt class="text-muted">Web Phone</dt><dd>{{ $crmCase->web_phone ?: '—' }}</dd></div>
            </dl>
        </section>

        <section class="crm-card p-4 sm:p-5">
            <h2 class="mb-3 text-base font-semibold text-primary">Description</h2>
            <p class="text-sm whitespace-pre-wrap">{{ $crmCase->description ?: '—' }}</p>
        </section>

        <section class="crm-card p-4 sm:p-5">
            <h2 class="mb-3 text-base font-semibold text-primary">Internal Comments</h2>
            <p class="text-sm whitespace-pre-wrap">{{ $crmCase->internal_comments ?: '—' }}</p>
        </section>

        <section class="crm-card p-4 sm:p-5 lg:col-span-2">
            <h2 class="mb-3 text-base font-semibold text-primary">System Information</h2>
            <dl class="grid gap-2 text-sm sm:grid-cols-2">
                <div><dt class="text-muted">Owner</dt><dd>{{ $crmCase->owner?->name ?? '—' }}</dd></div>
                <div><dt class="text-muted">Created By</dt><dd>{{ $crmCase->creator?->name ?? '—' }}</dd></div>
                <div><dt class="text-muted">Created Date</dt><dd>{{ $crmCase->created_at?->toDateTimeString() ?? '—' }}</dd></div>
                <div><dt class="text-muted">Last Modified By</dt><dd>{{ $crmCase->updater?->name ?? '—' }}</dd></div>
                <div><dt class="text-muted">Last Modified Date</dt><dd>{{ $crmCase->updated_at?->toDateTimeString() ?? '—' }}</dd></div>
            </dl>
        </section>
    </div>

    <div class="mt-6 grid gap-4 lg:grid-cols-3">
        <x-related-list title="Emails" empty="No emails yet." />
        <x-related-list title="Open Activities" empty="No open tasks.">
            @if (($openTasks ?? collect())->isNotEmpty())
                <ul class="divide-y divide-border text-sm">
                    @foreach ($openTasks as $task)
                        <li class="flex flex-wrap items-center justify-between gap-2 py-2">
                            <a href="{{ route('tasks.show', $task) }}">{{ $task->subject }}</a>
                            <span class="text-muted">{{ $task->due_date?->toDateString() ?? 'No due date' }}</span>
                        </li>
                    @endforeach
                </ul>
            @endif
            <p class="mt-2 text-xs text-muted">
                <a href="{{ route('tasks.create', ['related_type' => 'case', 'related_id' => $crmCase->id]) }}">New Task</a>
            </p>
        </x-related-list>
        <x-attachments-related-list :attachable="$crmCase" attachable-type="case" />
    </div>
@endsection
