@php
    $task = $task ?? null;
    $prefill = $prefill ?? [];
    $ownerOptions = $owners->mapWithKeys(fn ($u) => [$u->id => $u->name])->all();
    $contactOptions = $contacts->mapWithKeys(fn ($c) => [$c->id => $c->displayName()])->all();
@endphp

<section class="space-y-3" aria-labelledby="task-details-heading">
    <h2 id="task-details-heading" class="text-base font-semibold text-primary">Task Details</h2>
    <div class="grid gap-4 md:grid-cols-2">
        <x-form-field name="subject" label="Subject" :value="old('subject', $task->subject ?? '')" class="md:col-span-2" required />
        <x-form-field name="status" label="Status" :value="old('status', $task->status ?? 'not_started')" :options="$statuses" required />
        <x-form-field name="priority" label="Priority" :value="old('priority', $task->priority ?? 'normal')" :options="$priorities" required />
        <x-form-field name="due_date" label="Due Date" type="date" :value="old('due_date', optional($task->due_date ?? null)?->format('Y-m-d') ?? '')" />
        <x-form-field name="owner_id" label="Assigned To" :value="old('owner_id', $task->owner_id ?? auth()->id())" :options="$ownerOptions" />
        <x-form-field name="contact_id" label="Name (Contact)" :value="old('contact_id', $task->contact_id ?? '')" :options="$contactOptions" />
        <x-form-field
            name="related_type"
            label="Related To Type"
            :value="old('related_type', $task->related_type ?? ($prefill['related_type'] ?? ''))"
            :options="$relatedTypes"
        />
        <div class="space-y-1">
            <label for="related_id" class="block text-sm font-medium text-text">Related To Record</label>
            <select id="related_id" name="related_id" class="min-h-11 w-full rounded border border-black/20 bg-card px-3 py-2 text-sm">
                <option value="">Select…</option>
                @foreach ($relatedOptions as $type => $options)
                    <optgroup label="{{ $relatedTypes[$type] ?? $type }}" data-related-type="{{ $type }}">
                        @foreach ($options as $id => $label)
                            <option
                                value="{{ $id }}"
                                data-type="{{ $type }}"
                                @selected((string) old('related_id', $task->related_id ?? ($prefill['related_id'] ?? '')) === (string) $id && old('related_type', $task->related_type ?? ($prefill['related_type'] ?? '')) === $type)
                            >{{ $label }}</option>
                        @endforeach
                    </optgroup>
                @endforeach
            </select>
        </div>
        <label class="flex min-h-11 items-center gap-2 text-sm md:col-span-2">
            <input type="checkbox" name="reminder_set" value="1" class="size-4 rounded border-black/20" @checked(old('reminder_set', $task->reminder_set ?? false))>
            Set reminder
        </label>
        <x-form-field name="reminder_at" label="Reminder Date/Time" type="datetime-local" :value="old('reminder_at', isset($task) && $task->reminder_at ? $task->reminder_at->format('Y-m-d\TH:i') : '')" />
    </div>
</section>

<section class="space-y-3" aria-labelledby="task-comments-heading">
    <h2 id="task-comments-heading" class="text-base font-semibold text-primary">Comments</h2>
    <x-form-field name="comments" label="Comments" type="textarea" :value="old('comments', $task->comments ?? '')" />
</section>
