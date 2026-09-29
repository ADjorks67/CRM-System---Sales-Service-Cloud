@props([
    'id' => 'modal-'.uniqid(),
    'title' => 'Confirm',
    'confirmLabel' => 'Confirm',
    'cancelLabel' => 'Cancel',
])

<dialog
    id="{{ $id }}"
    {{ $attributes->merge(['class' => 'crm-modal max-w-md rounded border-0 bg-card p-0 shadow-lg backdrop:bg-black/40']) }}
    aria-labelledby="{{ $id }}-title"
>
    <form method="dialog" class="p-6">
        <h2 id="{{ $id }}-title" class="mb-2 text-lg font-semibold text-primary">{{ $title }}</h2>
        <div class="mb-6 text-sm text-text/80">
            {{ $slot }}
        </div>
        <div class="flex flex-wrap justify-end gap-2">
            <button type="submit" value="cancel" class="inline-flex min-h-11 items-center rounded border border-black/20 px-4 py-2 text-sm text-text">
                {{ $cancelLabel }}
            </button>
            <button type="submit" value="confirm" class="inline-flex min-h-11 items-center rounded bg-error px-4 py-2 text-sm font-semibold text-white">
                {{ $confirmLabel }}
            </button>
        </div>
    </form>
</dialog>
