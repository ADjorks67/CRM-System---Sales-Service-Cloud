@php
    $contact = $contact ?? null;
    $accountOptions = $accounts->mapWithKeys(fn ($a) => [$a->id => $a->name])->all();
    $reportsOptions = $reportsToContacts->mapWithKeys(fn ($c) => [$c->id => $c->displayName()])->all();
    $ownerOptions = $owners->mapWithKeys(fn ($u) => [$u->id => $u->name])->all();
@endphp

<section class="space-y-3" aria-labelledby="contact-details-heading">
    <h2 id="contact-details-heading" class="text-base font-semibold text-primary">Contact Details</h2>
    <div class="grid gap-4 md:grid-cols-2">
        <x-form-field name="salutation" label="Salutation" :value="old('salutation', $contact->salutation ?? '')" :options="$salutations" />
        <x-form-field name="first_name" label="First Name" :value="old('first_name', $contact->first_name ?? '')" />
        <x-form-field name="middle_name" label="Middle Name" :value="old('middle_name', $contact->middle_name ?? '')" />
        <x-form-field name="last_name" label="Last Name" :value="old('last_name', $contact->last_name ?? '')" required />
        <x-form-field name="account_id" label="Account Name" :value="old('account_id', $contact->account_id ?? request('account_id'))" :options="$accountOptions" required />
        <x-form-field name="title" label="Title" :value="old('title', $contact->title ?? '')" />
        <x-form-field name="department" label="Department" :value="old('department', $contact->department ?? '')" />
        <x-form-field name="reports_to_id" label="Reports To" :value="old('reports_to_id', $contact->reports_to_id ?? '')" :options="$reportsOptions" />
        <x-form-field name="phone" label="Phone" :value="old('phone', $contact->phone ?? '')" />
        <x-form-field name="mobile" label="Mobile" :value="old('mobile', $contact->mobile ?? '')" />
        <x-form-field name="home_phone" label="Home Phone" :value="old('home_phone', $contact->home_phone ?? '')" />
        <x-form-field name="other_phone" label="Other Phone" :value="old('other_phone', $contact->other_phone ?? '')" />
        <x-form-field name="email" label="Email" type="email" :value="old('email', $contact->email ?? '')" />
        <x-form-field name="fax" label="Fax" :value="old('fax', $contact->fax ?? '')" />
        <x-form-field name="assistant" label="Assistant" :value="old('assistant', $contact->assistant ?? '')" />
        <x-form-field name="asst_phone" label="Asst. Phone" :value="old('asst_phone', $contact->asst_phone ?? '')" />
        <x-form-field name="lead_source" label="Lead Source" :value="old('lead_source', $contact->lead_source ?? '')" :options="$leadSources" />
        <x-form-field name="birthdate" label="Birthdate" type="date" :value="old('birthdate', optional($contact->birthdate ?? null)?->format('Y-m-d'))" />
        @unless(isset($contact))
            <x-form-field name="owner_id" label="Owner" :value="old('owner_id', auth()->id())" :options="$ownerOptions" />
        @endunless
    </div>
</section>

<section class="space-y-3" aria-labelledby="mailing-heading">
    <h2 id="mailing-heading" class="text-base font-semibold text-primary">Mailing Address</h2>
    <div class="grid gap-4 md:grid-cols-2">
        <x-form-field name="mailing_street" label="Mailing Street" :value="old('mailing_street', $contact->mailing_street ?? '')" class="md:col-span-2" />
        <x-form-field name="mailing_city" label="Mailing City" :value="old('mailing_city', $contact->mailing_city ?? '')" />
        <x-form-field name="mailing_state" label="Mailing State/Province" :value="old('mailing_state', $contact->mailing_state ?? '')" />
        <x-form-field name="mailing_postal_code" label="Mailing Zip/Postal Code" :value="old('mailing_postal_code', $contact->mailing_postal_code ?? '')" />
        <x-form-field name="mailing_country" label="Mailing Country" :value="old('mailing_country', $contact->mailing_country ?? '')" />
    </div>
</section>

<section class="space-y-3" aria-labelledby="other-address-heading">
    <h2 id="other-address-heading" class="text-base font-semibold text-primary">Other Address</h2>
    <div class="grid gap-4 md:grid-cols-2">
        <x-form-field name="other_street" label="Other Street" :value="old('other_street', $contact->other_street ?? '')" class="md:col-span-2" />
        <x-form-field name="other_city" label="Other City" :value="old('other_city', $contact->other_city ?? '')" />
        <x-form-field name="other_state" label="Other State/Province" :value="old('other_state', $contact->other_state ?? '')" />
        <x-form-field name="other_postal_code" label="Other Zip/Postal Code" :value="old('other_postal_code', $contact->other_postal_code ?? '')" />
        <x-form-field name="other_country" label="Other Country" :value="old('other_country', $contact->other_country ?? '')" />
    </div>
</section>

<section class="space-y-3" aria-labelledby="contact-additional-heading">
    <h2 id="contact-additional-heading" class="text-base font-semibold text-primary">Additional Information</h2>
    <x-form-field name="description" label="Description" type="textarea" :value="old('description', $contact->description ?? '')" />
</section>
