@php
    $event = $event ?? null;
    $ownerOptions = $owners->mapWithKeys(fn ($u) => [$u->id => $u->name])->all();
    $contactOptions = $contacts->mapWithKeys(fn ($c) => [$c->id => trim($c->first_name.' '.$c->last_name)])->all();
    $defaultStart = old('starts_at', $event?->starts_at?->format('Y-m-d\TH:i') ?? ($prefill['starts_at'] ?? now()->format('Y-m-d\TH:i')));
    $defaultEnd = old('ends_at', $event?->ends_at?->format('Y-m-d\TH:i') ?? ($prefill['ends_at'] ?? now()->addHour()->format('Y-m-d\TH:i')));
@endphp

<section class="space-y-3" aria-labelledby="event-details-heading">
    <h2 id="event-details-heading" class="text-base font-semibold text-primary">Event Information</h2>
    <div class="grid gap-4 md:grid-cols-2">
        <x-form-field name="subject" label="Subject" :value="old('subject', $event->subject ?? '')" required class="md:col-span-2" />
        <x-form-field name="starts_at" label="Start" type="datetime-local" :value="$defaultStart" required />
        <x-form-field name="ends_at" label="End" type="datetime-local" :value="$defaultEnd" required />
        <label class="flex min-h-11 items-center gap-2 text-sm md:col-span-2">
            <input type="checkbox" name="is_all_day" value="1" class="size-4 rounded border-black/20" @checked(old('is_all_day', $event->is_all_day ?? ($prefill['is_all_day'] ?? false)))>
            All-Day Event
        </label>
        <x-form-field name="location" label="Location" :value="old('location', $event->location ?? '')" />
        <x-form-field name="show_as" label="Show Time As" :value="old('show_as', $event->show_as ?? 'busy')" :options="$showAsOptions" />
        <x-form-field name="owner_id" label="Assigned To" :value="old('owner_id', $event->owner_id ?? auth()->id())" :options="$ownerOptions" />
        <x-form-field name="name_contact_id" label="Name (Contact)" :value="old('name_contact_id', $event->name_contact_id ?? '')" :options="$contactOptions" />
        <x-form-field name="related_type" label="Related To Type" :value="old('related_type', $event->related_type ?? '')" :options="$relatedTypes" />
        <x-form-field name="related_id" label="Related To Id" type="number" :value="old('related_id', $event->related_id ?? '')" min="1" />
        <label class="flex min-h-11 items-center gap-2 text-sm md:col-span-2">
            <input type="checkbox" name="is_private" value="1" class="size-4 rounded border-black/20" @checked(old('is_private', $event->is_private ?? false))>
            Private (only visible to owner)
        </label>
    </div>
    <x-form-field name="description" label="Description" type="textarea" :value="old('description', $event->description ?? '')" />
</section>
