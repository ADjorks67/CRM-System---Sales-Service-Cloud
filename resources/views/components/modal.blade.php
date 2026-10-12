@props([
    'id' => 'modal-'.uniqid(),
    'title' => 'Confirm',
    'confirmLabel' => 'Confirm',
    'cancelLabel' => 'Cancel',
])

<dialog
    id="{{ $id }}"
    {{ $attributes->merge(['class' => 'crm-modal max-w-md rounded-xl border-0 bg-card p-0 shadow-lg backdrop:bg-black/40']) }}
    aria-labelledby="{{ $id }}-title"
>
    <form method="dialog" class="p-6">
        <h2 id="{{ $id }}-title" class="mb-2 text-lg font-semibold text-primary">{{ $title }}</h2>
        <div class="mb-6 text-sm text-muted">
            {{ $slot }}
        </div>
        <div class="flex flex-wrap justify-end gap-2">
            <button type="submit" value="cancel" class="crm-btn-secondary">
                {{ $cancelLabel }}
            </button>
            <button type="submit" value="confirm" class="crm-btn-danger">
                {{ $confirmLabel }}
            </button>
        </div>
    </form>
</dialog>
