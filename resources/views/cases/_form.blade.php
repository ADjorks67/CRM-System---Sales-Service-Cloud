@php
    $crmCase = $crmCase ?? null;
    $ownerOptions = $owners->mapWithKeys(fn ($u) => [$u->id => $u->name])->all();
    $accountOptions = $accounts->mapWithKeys(fn ($a) => [$a->id => $a->name])->all();
    $contactOptions = $contacts->mapWithKeys(fn ($c) => [$c->id => trim($c->first_name.' '.$c->last_name)])->all();
@endphp

<section class="space-y-3" aria-labelledby="case-details-heading">
    <h2 id="case-details-heading" class="text-base font-semibold text-primary">Case Information</h2>
    <div class="grid gap-4 md:grid-cols-2">
        @if ($crmCase)
            <div class="md:col-span-2">
                <p class="text-sm text-text/70">Case Number: <span class="font-medium text-text">{{ $crmCase->case_number }}</span></p>
            </div>
        @endif
        <x-form-field name="subject" label="Subject" :value="old('subject', $crmCase->subject ?? '')" class="md:col-span-2" />
        <x-form-field name="status" label="Status" :value="old('status', $crmCase->status ?? 'new')" :options="$statuses" required />
        <x-form-field name="priority" label="Priority" :value="old('priority', $crmCase->priority ?? 'medium')" :options="$priorities" />
        <x-form-field name="origin" label="Case Origin" :value="old('origin', $crmCase->origin ?? 'web')" :options="$origins" required />
        <x-form-field name="type" label="Type" :value="old('type', $crmCase->type ?? '')" :options="$types" />
        <x-form-field name="reason" label="Case Reason" :value="old('reason', $crmCase->reason ?? '')" :options="$reasons" />
        <x-form-field name="account_id" label="Account" :value="old('account_id', $crmCase->account_id ?? '')" :options="$accountOptions" />
        <x-form-field name="contact_id" label="Contact" :value="old('contact_id', $crmCase->contact_id ?? '')" :options="$contactOptions" />
        @unless(isset($crmCase))
            <x-form-field name="owner_id" label="Owner" :value="old('owner_id', auth()->id())" :options="$ownerOptions" />
        @endunless
    </div>
</section>

<section class="space-y-3" aria-labelledby="case-web-heading">
    <h2 id="case-web-heading" class="text-base font-semibold text-primary">Web Information</h2>
    <div class="grid gap-4 md:grid-cols-2">
        <x-form-field name="web_name" label="Web Name" :value="old('web_name', $crmCase->web_name ?? '')" />
        <x-form-field name="web_email" label="Web Email" type="email" :value="old('web_email', $crmCase->web_email ?? '')" />
        <x-form-field name="web_company" label="Web Company" :value="old('web_company', $crmCase->web_company ?? '')" />
        <x-form-field name="web_phone" label="Web Phone" :value="old('web_phone', $crmCase->web_phone ?? '')" />
    </div>
</section>

<section class="space-y-3" aria-labelledby="case-description-heading">
    <h2 id="case-description-heading" class="text-base font-semibold text-primary">Description</h2>
    <x-form-field name="description" label="Description" type="textarea" :value="old('description', $crmCase->description ?? '')" />
    <x-form-field name="internal_comments" label="Internal Comments" type="textarea" :value="old('internal_comments', $crmCase->internal_comments ?? '')" />
</section>
