@php
    $account = $account ?? null;
    $parentOptions = $parentAccounts->mapWithKeys(fn ($a) => [$a->id => $a->name])->all();
    $ownerOptions = $owners->mapWithKeys(fn ($u) => [$u->id => $u->name])->all();
@endphp

<section class="space-y-3" aria-labelledby="account-details-heading">
    <h2 id="account-details-heading" class="text-base font-semibold text-primary">Account Details</h2>
    <div class="grid gap-4 md:grid-cols-2">
        <x-form-field name="name" label="Account Name" :value="old('name', $account->name ?? '')" required />
        <x-form-field name="parent_account_id" label="Parent Account" :value="old('parent_account_id', $account->parent_account_id ?? '')" :options="$parentOptions" />
        <x-form-field name="phone" label="Phone" :value="old('phone', $account->phone ?? '')" />
        <x-form-field name="fax" label="Fax" :value="old('fax', $account->fax ?? '')" />
        <x-form-field name="website" label="Website" :value="old('website', $account->website ?? '')" />
        <x-form-field name="type" label="Type" :value="old('type', $account->type ?? '')" :options="$types" />
        <x-form-field name="industry" label="Industry" :value="old('industry', $account->industry ?? '')" :options="$industries" />
        <x-form-field name="employees" label="Employees" type="number" :value="old('employees', $account->employees ?? '')" />
        <x-form-field name="annual_revenue" label="Annual Revenue" type="number" :value="old('annual_revenue', $account->annual_revenue ?? '')" />
        @unless(isset($account))
            <x-form-field name="owner_id" label="Owner" :value="old('owner_id', auth()->id())" :options="$ownerOptions" />
        @endunless
    </div>
</section>

<section class="space-y-3" aria-labelledby="billing-heading">
    <h2 id="billing-heading" class="text-base font-semibold text-primary">Billing Address</h2>
    <div class="grid gap-4 md:grid-cols-2">
        <x-form-field name="billing_street" label="Billing Street" :value="old('billing_street', $account->billing_street ?? '')" class="md:col-span-2" />
        <x-form-field name="billing_city" label="Billing City" :value="old('billing_city', $account->billing_city ?? '')" />
        <x-form-field name="billing_state" label="Billing State/Province" :value="old('billing_state', $account->billing_state ?? '')" />
        <x-form-field name="billing_postal_code" label="Billing Zip/Postal Code" :value="old('billing_postal_code', $account->billing_postal_code ?? '')" />
        <x-form-field name="billing_country" label="Billing Country" :value="old('billing_country', $account->billing_country ?? '')" />
    </div>
</section>

<section class="space-y-3" aria-labelledby="shipping-heading">
    <div class="flex flex-wrap items-center justify-between gap-2">
        <h2 id="shipping-heading" class="text-base font-semibold text-primary">Shipping Address</h2>
        <label class="flex min-h-11 items-center gap-2 text-sm">
            <input type="checkbox" name="copy_billing_to_shipping" value="1" class="size-4 rounded border-black/20" data-copy-billing @checked(old('copy_billing_to_shipping'))>
            Copy Billing Address to Shipping Address
        </label>
    </div>
    <div class="grid gap-4 md:grid-cols-2">
        <x-form-field name="shipping_street" label="Shipping Street" :value="old('shipping_street', $account->shipping_street ?? '')" class="md:col-span-2" />
        <x-form-field name="shipping_city" label="Shipping City" :value="old('shipping_city', $account->shipping_city ?? '')" />
        <x-form-field name="shipping_state" label="Shipping State/Province" :value="old('shipping_state', $account->shipping_state ?? '')" />
        <x-form-field name="shipping_postal_code" label="Shipping Zip/Postal Code" :value="old('shipping_postal_code', $account->shipping_postal_code ?? '')" />
        <x-form-field name="shipping_country" label="Shipping Country" :value="old('shipping_country', $account->shipping_country ?? '')" />
    </div>
</section>

<section class="space-y-3" aria-labelledby="additional-heading">
    <h2 id="additional-heading" class="text-base font-semibold text-primary">Additional Information</h2>
    <x-form-field name="description" label="Description" type="textarea" :value="old('description', $account->description ?? '')" />
</section>
