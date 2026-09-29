@extends('layouts.app')

@section('title', $account->name.' — '.config('app.name'))

@section('content')
    <div class="mb-4 flex flex-wrap items-start justify-between gap-3">
        <div>
            <h1>{{ $account->name }}</h1>
            <p class="text-sm text-text/70">Owner: {{ $account->owner?->name ?? '—' }}</p>
        </div>
        <div class="flex flex-wrap gap-2">
            @can('update', $account)
                <a href="{{ route('accounts.edit', $account) }}" class="inline-flex min-h-11 items-center rounded bg-secondary px-4 py-2 text-sm font-semibold text-white no-underline">Edit</a>
            @endcan
            @can('delete', $account)
                <form method="post" action="{{ route('accounts.destroy', $account) }}" onsubmit="return confirm('Delete this account?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="inline-flex min-h-11 items-center rounded border border-error px-4 py-2 text-sm font-semibold text-error">Delete</button>
                </form>
            @endcan
        </div>
    </div>

    @can('changeOwner', $account)
        <form method="post" action="{{ route('accounts.change-owner', $account) }}" class="mb-6 flex flex-wrap items-end gap-2 rounded bg-card p-4 shadow-[var(--shadow-card)]">
            @csrf
            <x-form-field name="owner_id" label="Change Owner" :value="$account->owner_id" :options="$owners->mapWithKeys(fn ($u) => [$u->id => $u->name])->all()" class="min-w-[14rem]" required />
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
            <h2 class="mb-3 text-base font-semibold text-primary">Account Details</h2>
            <dl class="grid gap-2 text-sm sm:grid-cols-2">
                <div><dt class="text-text/60">Parent Account</dt><dd>{{ $account->parentAccount?->name ?? '—' }}</dd></div>
                <div><dt class="text-text/60">Type</dt><dd>{{ $typeLabel ?: '—' }}</dd></div>
                <div><dt class="text-text/60">Industry</dt><dd>{{ $industryLabel ?: '—' }}</dd></div>
                <div><dt class="text-text/60">Phone</dt><dd>{{ $account->phone ?: '—' }}</dd></div>
                <div><dt class="text-text/60">Fax</dt><dd>{{ $account->fax ?: '—' }}</dd></div>
                <div><dt class="text-text/60">Website</dt><dd>{{ $account->website ?: '—' }}</dd></div>
                <div><dt class="text-text/60">Employees</dt><dd>{{ $account->employees ?? '—' }}</dd></div>
                <div><dt class="text-text/60">Annual Revenue</dt><dd>{{ $account->annual_revenue ?? '—' }}</dd></div>
            </dl>
        </section>

        <section class="rounded bg-card p-4 shadow-[var(--shadow-card)]">
            <h2 class="mb-3 text-base font-semibold text-primary">Address Information</h2>
            <div class="grid gap-4 text-sm sm:grid-cols-2">
                <div>
                    <h3 class="mb-1 font-medium">Billing</h3>
                    <p>{{ $account->billing_street ?: '—' }}</p>
                    <p>{{ collect([$account->billing_city, $account->billing_state, $account->billing_postal_code])->filter()->implode(', ') ?: '—' }}</p>
                    <p>{{ $account->billing_country ?: '—' }}</p>
                </div>
                <div>
                    <h3 class="mb-1 font-medium">Shipping</h3>
                    <p>{{ $account->shipping_street ?: '—' }}</p>
                    <p>{{ collect([$account->shipping_city, $account->shipping_state, $account->shipping_postal_code])->filter()->implode(', ') ?: '—' }}</p>
                    <p>{{ $account->shipping_country ?: '—' }}</p>
                </div>
            </div>
        </section>

        <section class="rounded bg-card p-4 shadow-[var(--shadow-card)]">
            <h2 class="mb-3 text-base font-semibold text-primary">Additional Information</h2>
            <p class="text-sm whitespace-pre-wrap">{{ $account->description ?: '—' }}</p>
        </section>

        <section class="rounded bg-card p-4 shadow-[var(--shadow-card)]">
            <h2 class="mb-3 text-base font-semibold text-primary">System Information</h2>
            <dl class="grid gap-2 text-sm sm:grid-cols-2">
                <div><dt class="text-text/60">Owner</dt><dd>{{ $account->owner?->name ?? '—' }}</dd></div>
                <div><dt class="text-text/60">Created By</dt><dd>{{ $account->creator?->name ?? '—' }}</dd></div>
                <div><dt class="text-text/60">Created Date</dt><dd>{{ $account->created_at?->toDateTimeString() ?? '—' }}</dd></div>
                <div><dt class="text-text/60">Last Modified By</dt><dd>{{ $account->updater?->name ?? '—' }}</dd></div>
                <div><dt class="text-text/60">Last Modified Date</dt><dd>{{ $account->updated_at?->toDateTimeString() ?? '—' }}</dd></div>
            </dl>
        </section>
    </div>

    <div class="mt-6 grid gap-4">
        <x-related-list title="Contacts" empty="No contacts for this account.">
            @if ($account->contacts->isNotEmpty())
                <ul class="divide-y divide-black/10 text-sm">
                    @foreach ($account->contacts as $contact)
                        <li class="flex flex-wrap items-center justify-between gap-2 py-2">
                            <a href="{{ route('contacts.show', $contact) }}">{{ $contact->displayName() }}</a>
                            <span class="text-text/60">{{ $contact->email ?: '—' }}</span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </x-related-list>
        <x-related-list title="Opportunities" empty="No opportunities for this account.">
            @if ($account->opportunities->isNotEmpty())
                <ul class="divide-y divide-black/10 text-sm">
                    @foreach ($account->opportunities as $opportunity)
                        <li class="flex flex-wrap items-center justify-between gap-2 py-2">
                            <a href="{{ route('opportunities.show', $opportunity) }}">{{ $opportunity->name }}</a>
                            <span class="text-text/60">{{ \App\Support\PicklistOptions::options('opportunity_stage')[$opportunity->stage] ?? $opportunity->stage }}</span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </x-related-list>
        <x-related-list title="Cases" empty="No cases for this account.">
            @if ($account->cases->isNotEmpty())
                <ul class="divide-y divide-black/10 text-sm">
                    @foreach ($account->cases as $crmCase)
                        <li class="flex flex-wrap items-center justify-between gap-2 py-2">
                            <a href="{{ route('cases.show', $crmCase) }}">{{ $crmCase->case_number }} — {{ $crmCase->displayName() }}</a>
                            <span class="text-text/60">{{ \App\Support\PicklistOptions::options('case_status')[$crmCase->status] ?? $crmCase->status }}</span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </x-related-list>
        <x-related-list title="Activities" empty="Activities arrive in Phase 4." />
    </div>
@endsection
