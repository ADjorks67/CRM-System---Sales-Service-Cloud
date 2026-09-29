@php
    $opportunity = $opportunity ?? null;
    $accountOptions = $accounts->mapWithKeys(fn ($a) => [$a->id => $a->name])->all();
    $ownerOptions = $owners->mapWithKeys(fn ($u) => [$u->id => $u->name])->all();
    $isEdit = isset($opportunity);
@endphp

<section class="space-y-3" aria-labelledby="opportunity-details-heading">
    <h2 id="opportunity-details-heading" class="text-base font-semibold text-primary">Opportunity Information</h2>
    <div class="grid gap-4 md:grid-cols-2">
        <x-form-field name="name" label="Opportunity Name" :value="old('name', $opportunity->name ?? '')" required />
        <x-form-field name="account_id" label="Account" :value="old('account_id', $opportunity->account_id ?? '')" :options="$accountOptions" required />
        @unless($isEdit)
            <x-form-field name="stage" label="Stage" :value="old('stage', $opportunity->stage ?? 'qualification')" :options="$stages" required />
        @endunless
        <x-form-field name="close_date" label="Close Date" type="date" :value="old('close_date', isset($opportunity?->close_date) ? $opportunity->close_date->format('Y-m-d') : '')" required />
        <x-form-field name="amount" label="Amount" type="number" :value="old('amount', $opportunity->amount ?? '')" step="0.01" min="0" />
        <x-form-field name="type" label="Type" :value="old('type', $opportunity->type ?? '')" :options="$types" />
        <x-form-field name="lead_source" label="Lead Source" :value="old('lead_source', $opportunity->lead_source ?? '')" :options="$leadSources" />
        <x-form-field name="next_step" label="Next Step" :value="old('next_step', $opportunity->next_step ?? '')" class="md:col-span-2" />
        @if ($isEdit)
            <x-form-field name="probability" label="Probability (%)" type="number" :value="old('probability', $opportunity->probability ?? '')" min="0" max="100" />
        @endif
        @unless($isEdit)
            <x-form-field name="owner_id" label="Owner" :value="old('owner_id', auth()->id())" :options="$ownerOptions" />
        @endunless
    </div>
    <x-form-field name="description" label="Description" type="textarea" :value="old('description', $opportunity->description ?? '')" class="md:col-span-2" />
</section>
