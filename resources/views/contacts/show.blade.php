@extends('layouts.app')

@section('title', $contact->displayName().' — '.config('app.name'))

@section('content')
    <x-breadcrumbs :items="[
        ['label' => 'Contacts', 'url' => route('contacts.index')],
        ['label' => $contact->displayName()],
    ]" />

    <div class="mb-4 flex flex-wrap items-start justify-between gap-3">
        <div>
            <h1>{{ trim(($salutationLabel ? $salutationLabel.' ' : '').$contact->displayName()) }}</h1>
            <p class="text-sm text-text/70">
                @if ($contact->account)
                    Account:
                    <a href="{{ route('accounts.show', $contact->account) }}">{{ $contact->account->name }}</a>
                @endif
                · Owner: {{ $contact->owner?->name ?? '—' }}
            </p>
        </div>
        <div class="flex flex-wrap gap-2">
            @can('update', $contact)
                <a href="{{ route('contacts.edit', $contact) }}" class="inline-flex min-h-11 items-center rounded bg-secondary px-4 py-2 text-sm font-semibold text-white no-underline">Edit</a>
            @endcan
            @can('delete', $contact)
                <form method="post" action="{{ route('contacts.destroy', $contact) }}" onsubmit="return confirm('Delete this contact?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="inline-flex min-h-11 items-center rounded border border-error px-4 py-2 text-sm font-semibold text-error">Delete</button>
                </form>
            @endcan
        </div>
    </div>

    @can('changeOwner', $contact)
        <form method="post" action="{{ route('contacts.change-owner', $contact) }}" class="mb-6 flex flex-wrap items-end gap-2 rounded bg-card p-4 shadow-[var(--shadow-card)]">
            @csrf
            <x-form-field name="owner_id" label="Change Owner" :value="$contact->owner_id" :options="$owners->mapWithKeys(fn ($u) => [$u->id => $u->name])->all()" class="min-w-[14rem]" required />
            <label class="flex min-h-11 items-center gap-2 text-sm">
                <input type="checkbox" name="notify_new_owner" value="1" class="size-4 rounded border-black/20">
                Notify new owner (stub)
            </label>
            <button type="submit" class="inline-flex min-h-11 items-center rounded border border-black/20 px-4 py-2 text-sm">Change Owner</button>
        </form>
    @endcan

    <div class="grid gap-4 lg:grid-cols-2">
        <section class="rounded bg-card p-4 shadow-[var(--shadow-card)]">
            <h2 class="mb-3 text-base font-semibold text-primary">Contact Details</h2>
            <dl class="grid gap-2 text-sm sm:grid-cols-2">
                <div><dt class="text-text/60">Title</dt><dd>{{ $contact->title ?: '—' }}</dd></div>
                <div><dt class="text-text/60">Department</dt><dd>{{ $contact->department ?: '—' }}</dd></div>
                <div><dt class="text-text/60">Email</dt><dd>{{ $contact->email ?: '—' }}</dd></div>
                <div><dt class="text-text/60">Phone</dt><dd>{{ $contact->phone ?: '—' }}</dd></div>
                <div><dt class="text-text/60">Mobile</dt><dd>{{ $contact->mobile ?: '—' }}</dd></div>
                <div><dt class="text-text/60">Reports To</dt><dd>{{ $contact->reportsTo?->displayName() ?? '—' }}</dd></div>
                <div><dt class="text-text/60">Lead Source</dt><dd>{{ $leadSourceLabel ?: '—' }}</dd></div>
                <div><dt class="text-text/60">Birthdate</dt><dd>{{ $contact->birthdate?->toDateString() ?? '—' }}</dd></div>
            </dl>
        </section>

        <section class="rounded bg-card p-4 shadow-[var(--shadow-card)]">
            <h2 class="mb-3 text-base font-semibold text-primary">Address Information</h2>
            <div class="grid gap-4 text-sm sm:grid-cols-2">
                <div>
                    <h3 class="mb-1 font-medium">Mailing</h3>
                    <p>{{ $contact->mailing_street ?: '—' }}</p>
                    <p>{{ collect([$contact->mailing_city, $contact->mailing_state, $contact->mailing_postal_code])->filter()->implode(', ') ?: '—' }}</p>
                    <p>{{ $contact->mailing_country ?: '—' }}</p>
                </div>
                <div>
                    <h3 class="mb-1 font-medium">Other</h3>
                    <p>{{ $contact->other_street ?: '—' }}</p>
                    <p>{{ collect([$contact->other_city, $contact->other_state, $contact->other_postal_code])->filter()->implode(', ') ?: '—' }}</p>
                    <p>{{ $contact->other_country ?: '—' }}</p>
                </div>
            </div>
        </section>

        <section class="rounded bg-card p-4 shadow-[var(--shadow-card)]">
            <h2 class="mb-3 text-base font-semibold text-primary">Additional Information</h2>
            <p class="text-sm whitespace-pre-wrap">{{ $contact->description ?: '—' }}</p>
        </section>

        <section class="rounded bg-card p-4 shadow-[var(--shadow-card)]">
            <h2 class="mb-3 text-base font-semibold text-primary">System Information</h2>
            <dl class="grid gap-2 text-sm sm:grid-cols-2">
                <div><dt class="text-text/60">Owner</dt><dd>{{ $contact->owner?->name ?? '—' }}</dd></div>
                <div><dt class="text-text/60">Created By</dt><dd>{{ $contact->creator?->name ?? '—' }}</dd></div>
                <div><dt class="text-text/60">Created Date</dt><dd>{{ $contact->created_at?->toDateTimeString() ?? '—' }}</dd></div>
                <div><dt class="text-text/60">Last Modified By</dt><dd>{{ $contact->updater?->name ?? '—' }}</dd></div>
                <div><dt class="text-text/60">Last Modified Date</dt><dd>{{ $contact->updated_at?->toDateTimeString() ?? '—' }}</dd></div>
            </dl>
        </section>
    </div>

    <div class="mt-6 grid gap-4">
        <x-related-list title="Cases" empty="No cases for this contact.">
            @if ($contact->cases->isNotEmpty())
                <ul class="divide-y divide-black/10 text-sm">
                    @foreach ($contact->cases as $crmCase)
                        <li class="flex flex-wrap items-center justify-between gap-2 py-2">
                            <a href="{{ route('cases.show', $crmCase) }}">{{ $crmCase->case_number }} — {{ $crmCase->displayName() }}</a>
                            <span class="text-text/60">{{ \App\Support\PicklistOptions::options('case_status')[$crmCase->status] ?? $crmCase->status }}</span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </x-related-list>
        <x-related-list title="Opportunities" empty="No opportunities on this contact's account.">
            @if ($accountOpportunities->isNotEmpty())
                <ul class="divide-y divide-black/10 text-sm">
                    @foreach ($accountOpportunities as $opportunity)
                        <li class="flex flex-wrap items-center justify-between gap-2 py-2">
                            <a href="{{ route('opportunities.show', $opportunity) }}">{{ $opportunity->name }}</a>
                            <span class="text-text/60">{{ \App\Support\PicklistOptions::options('opportunity_stage')[$opportunity->stage] ?? $opportunity->stage }}</span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </x-related-list>
        <x-related-list title="Activities" empty="No open tasks for this contact.">
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
                <a href="{{ route('tasks.create', ['related_type' => 'contact', 'related_id' => $contact->id]) }}">New Task</a>
                · Events arrive with Dev B calendar
            </p>
        </x-related-list>
        <x-attachments-related-list :attachable="$contact" attachable-type="contact" />
    </div>
@endsection
