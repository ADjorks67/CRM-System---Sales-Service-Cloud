@props([
    'attachable',
    'attachableType',
    'attachments' => null,
])

@php
    $items = $attachments ?? ($attachable->relationLoaded('attachments') ? $attachable->attachments : collect());
@endphp

<x-related-list title="Notes & Attachments" :empty="$items->isEmpty() ? 'No attachments yet.' : null">
    @if ($items->isNotEmpty())
        <ul class="divide-y divide-black/10 text-sm">
            @foreach ($items as $attachment)
                <li class="flex flex-wrap items-center justify-between gap-2 py-2">
                    <div>
                        <span class="font-medium">{{ $attachment->original_name }}</span>
                        <span class="text-text/60">({{ number_format($attachment->size_bytes / 1024, 1) }} KB)</span>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        @if ($attachment->isImage())
                            <a href="{{ route('attachments.preview', $attachment) }}" class="text-secondary no-underline" target="_blank" rel="noopener">Preview</a>
                        @endif
                        <a href="{{ route('attachments.download', $attachment) }}" class="text-secondary no-underline">Download</a>
                        @can('delete', $attachment)
                            <form method="post" action="{{ route('attachments.destroy', $attachment) }}" class="inline" onsubmit="return confirm('Delete this attachment?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-error">Delete</button>
                            </form>
                        @endcan
                    </div>
                </li>
            @endforeach
        </ul>
    @endif

    @can('update', $attachable)
        <form method="post" action="{{ route('attachments.store') }}" enctype="multipart/form-data" class="mt-3 flex flex-wrap items-end gap-2">
            @csrf
            <input type="hidden" name="attachable_type" value="{{ $attachableType }}">
            <input type="hidden" name="attachable_id" value="{{ $attachable->getKey() }}">
            <label class="block min-w-[14rem] flex-1 text-sm">
                <span class="mb-1 block font-medium">Upload file (max 25MB)</span>
                <input type="file" name="file" required class="min-h-11 w-full rounded border border-black/20 bg-page px-3 py-2 text-sm">
            </label>
            <button type="submit" class="inline-flex min-h-11 items-center rounded bg-secondary px-4 py-2 text-sm font-semibold text-white">Upload</button>
        </form>
        @error('file')
            <p class="mt-1 text-sm text-error">{{ $message }}</p>
        @enderror
    @endcan
</x-related-list>
