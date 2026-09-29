@php
    $lead = $lead ?? null;
    $ownerOptions = $owners->mapWithKeys(fn ($u) => [$u->id => $u->name])->all();
@endphp

<section class="space-y-3" aria-labelledby="lead-details-heading">
    <h2 id="lead-details-heading" class="text-base font-semibold text-primary">Lead Details</h2>
    <div class="grid gap-4 md:grid-cols-2">
        <x-form-field name="salutation" label="Salutation" :value="old('salutation', $lead->salutation ?? '')" :options="$salutations" />
        <x-form-field name="first_name" label="First Name" :value="old('first_name', $lead->first_name ?? '')" />
        <x-form-field name="last_name" label="Last Name" :value="old('last_name', $lead->last_name ?? '')" required />
        <x-form-field name="company" label="Company" :value="old('company', $lead->company ?? '')" required />
        <x-form-field name="title" label="Title" :value="old('title', $lead->title ?? '')" />
        <x-form-field name="status" label="Lead Status" :value="old('status', $lead->status?->value ?? 'new')" :options="$statuses" required />
        <x-form-field name="phone" label="Phone" :value="old('phone', $lead->phone ?? '')" />
        <x-form-field name="mobile" label="Mobile" :value="old('mobile', $lead->mobile ?? '')" />
        <x-form-field name="email" label="Email" type="email" :value="old('email', $lead->email ?? '')" />
        <x-form-field name="website" label="Website" :value="old('website', $lead->website ?? '')" />
        <x-form-field name="lead_source" label="Lead Source" :value="old('lead_source', $lead->lead_source ?? '')" :options="$leadSources" />
        <x-form-field name="industry" label="Industry" :value="old('industry', $lead->industry ?? '')" :options="$industries" />
        <x-form-field name="rating" label="Rating" :value="old('rating', $lead->rating ?? '')" :options="$ratings" />
        <x-form-field name="annual_revenue" label="Annual Revenue" type="number" :value="old('annual_revenue', $lead->annual_revenue ?? '')" />
        <x-form-field name="number_of_employees" label="Number of Employees" type="number" :value="old('number_of_employees', $lead->number_of_employees ?? '')" />
        @unless(isset($lead))
            <x-form-field name="owner_id" label="Owner" :value="old('owner_id', auth()->id())" :options="$ownerOptions" />
        @endunless
    </div>
</section>

<section class="space-y-3" aria-labelledby="lead-address-heading">
    <h2 id="lead-address-heading" class="text-base font-semibold text-primary">Address Information</h2>
    <div class="grid gap-4 md:grid-cols-2">
        <x-form-field name="street" label="Street" :value="old('street', $lead->street ?? '')" class="md:col-span-2" />
        <x-form-field name="city" label="City" :value="old('city', $lead->city ?? '')" />
        <x-form-field name="state" label="State/Province" :value="old('state', $lead->state ?? '')" />
        <x-form-field name="postal_code" label="Zip/Postal Code" :value="old('postal_code', $lead->postal_code ?? '')" />
        <x-form-field name="country" label="Country" :value="old('country', $lead->country ?? '')" />
    </div>
</section>

<section class="space-y-3" aria-labelledby="lead-additional-heading">
    <h2 id="lead-additional-heading" class="text-base font-semibold text-primary">Additional Information</h2>
    <x-form-field name="description" label="Description" type="textarea" :value="old('description', $lead->description ?? '')" />
</section>
