@extends('layouts.app')

@section('title', 'Convert Lead — '.config('app.name'))

@section('content')
    <div class="mb-4">
        <h1>Convert Lead</h1>
        <p class="text-sm text-text/70">
            {{ $lead->displayName() }} · {{ $lead->company }}
            — create or match Account, create Contact, optionally create Opportunity (FR-LEAD-005).
        </p>
    </div>

    <form method="post" action="{{ route('leads.convert.store', $lead) }}" class="space-y-4 rounded bg-card p-4 shadow-[var(--shadow-card)]">
        @csrf

        <fieldset class="space-y-3 border-0 p-0">
            <legend class="text-base font-semibold text-primary">Account</legend>
            <label class="flex min-h-11 items-center gap-2 text-sm">
                <input type="radio" name="account_action" value="create" class="size-4" @checked(old('account_action', 'create') === 'create')>
                Create new Account
            </label>
            <x-form-field name="account_name" label="Account Name" :value="old('account_name', $lead->company)" />

            <label class="flex min-h-11 items-center gap-2 text-sm">
                <input type="radio" name="account_action" value="match" class="size-4" @checked(old('account_action') === 'match')>
                Match existing Account
            </label>
            @php
                $matchOptions = $matchingAccounts->mapWithKeys(fn ($a) => [$a->id => $a->name])->all();
            @endphp
            @if ($matchOptions === [])
                <p class="text-sm text-text/60">No accounts match company “{{ $lead->company }}”. Search by creating or pick after changing company.</p>
            @else
                <x-form-field name="account_id" label="Existing Account" :value="old('account_id')" :options="$matchOptions" />
            @endif
        </fieldset>

        <fieldset class="space-y-3 border-0 p-0">
            <legend class="text-base font-semibold text-primary">Contact</legend>
            <p class="text-sm text-text/70">A Contact will be created from this lead’s name and details.</p>
        </fieldset>

        <fieldset class="space-y-3 border-0 p-0">
            <legend class="text-base font-semibold text-primary">Opportunity (optional)</legend>
            <label class="flex min-h-11 items-center gap-2 text-sm">
                <input type="checkbox" name="create_opportunity" value="1" class="size-4 rounded border-black/20" @checked(old('create_opportunity'))>
                Create Opportunity
            </label>
            <div class="grid gap-4 md:grid-cols-2">
                <x-form-field name="opportunity_name" label="Opportunity Name" :value="old('opportunity_name', ($lead->company ?: $lead->displayName()).' — Opportunity')" />
                <x-form-field name="opportunity_amount" label="Amount" type="number" :value="old('opportunity_amount')" step="0.01" min="0" />
                <x-form-field name="opportunity_close_date" label="Close Date" type="date" :value="old('opportunity_close_date', now()->addMonth()->toDateString())" />
                <x-form-field name="opportunity_stage" label="Stage" :value="old('opportunity_stage', 'qualification')" :options="$stageOptions" />
            </div>
        </fieldset>

        <p class="text-sm text-text/70">Open events related to this lead will transfer to the new Account/Opportunity. Tasks transfer when Dev A ships FR-TASK-*.</p>

        <div class="flex flex-wrap gap-2">
            <button type="submit" class="inline-flex min-h-11 items-center rounded bg-secondary px-4 py-2 text-sm font-semibold text-white">Convert</button>
            <a href="{{ route('leads.show', $lead) }}" class="inline-flex min-h-11 items-center rounded px-4 py-2 text-sm no-underline">Cancel</a>
        </div>
    </form>
@endsection
