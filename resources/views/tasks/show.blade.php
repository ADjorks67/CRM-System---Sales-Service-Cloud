@extends('layouts.app')

@section('title', $task->subject.' — '.config('app.name'))

@section('content')
    <div class="mb-4 flex flex-wrap items-start justify-between gap-3">
        <div>
            <h1>{{ $task->subject }}</h1>
            <p class="text-sm text-text/70">
                {{ $statusLabel }} · {{ $priorityLabel }} · Assigned to {{ $task->owner?->name ?? '—' }}
                @if ($task->isOverdue())
                    <span class="font-medium text-error">· Overdue</span>
                @endif
            </p>
        </div>
        <div class="flex flex-wrap gap-2">
            @can('complete', $task)
                @unless ($task->isCompleted())
                    <form method="post" action="{{ route('tasks.complete', $task) }}">
                        @csrf
                        <button type="submit" class="inline-flex min-h-11 items-center rounded bg-success px-4 py-2 text-sm font-semibold text-white">Mark Complete</button>
                    </form>
                @endunless
            @endcan
            @can('update', $task)
                <a href="{{ route('tasks.edit', $task) }}" class="inline-flex min-h-11 items-center rounded bg-secondary px-4 py-2 text-sm font-semibold text-white no-underline">Edit</a>
            @endcan
            @can('delete', $task)
                <form method="post" action="{{ route('tasks.destroy', $task) }}" onsubmit="return confirm('Delete this task?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="inline-flex min-h-11 items-center rounded border border-error px-4 py-2 text-sm font-semibold text-error">Delete</button>
                </form>
            @endcan
        </div>
    </div>

    @can('changeOwner', $task)
        <form method="post" action="{{ route('tasks.change-owner', $task) }}" class="mb-6 flex flex-wrap items-end gap-2 rounded bg-card p-4 shadow-[var(--shadow-card)]">
            @csrf
            <x-form-field name="owner_id" label="Change Assigned To" :value="$task->owner_id" :options="$owners->mapWithKeys(fn ($u) => [$u->id => $u->name])->all()" class="min-w-[14rem]" required />
            <label class="flex min-h-11 items-center gap-2 text-sm">
                <input type="checkbox" name="notify_new_owner" value="1" class="size-4 rounded border-black/20" checked>
                Notify new owner
            </label>
            <button type="submit" class="inline-flex min-h-11 items-center rounded border border-black/20 px-4 py-2 text-sm">Change Owner</button>
        </form>
    @endcan

    <div class="grid gap-4 lg:grid-cols-2">
        <section class="rounded bg-card p-4 shadow-[var(--shadow-card)]">
            <h2 class="mb-3 text-base font-semibold text-primary">Task Information</h2>
            <dl class="grid gap-2 text-sm sm:grid-cols-2">
                <div><dt class="text-text/60">Status</dt><dd>{{ $statusLabel }}</dd></div>
                <div><dt class="text-text/60">Priority</dt><dd>{{ $priorityLabel }}</dd></div>
                <div><dt class="text-text/60">Due Date</dt><dd>{{ $task->due_date?->toDateString() ?? '—' }}</dd></div>
                <div><dt class="text-text/60">Related To</dt><dd>{{ $relatedLabel }}</dd></div>
                <div><dt class="text-text/60">Name</dt><dd>{{ $task->contact?->displayName() ?? '—' }}</dd></div>
                <div><dt class="text-text/60">Reminder</dt><dd>{{ $task->reminder_set ? ($task->reminder_at?->toDateTimeString() ?? 'Set') : '—' }}</dd></div>
            </dl>
        </section>
        <section class="rounded bg-card p-4 shadow-[var(--shadow-card)]">
            <h2 class="mb-3 text-base font-semibold text-primary">Comments</h2>
            <p class="whitespace-pre-wrap text-sm">{{ $task->comments ?: '—' }}</p>
        </section>
        <section class="rounded bg-card p-4 shadow-[var(--shadow-card)] lg:col-span-2">
            <h2 class="mb-3 text-base font-semibold text-primary">System Information</h2>
            <dl class="grid gap-2 text-sm sm:grid-cols-2">
                <div><dt class="text-text/60">Assigned To</dt><dd>{{ $task->owner?->name ?? '—' }}</dd></div>
                <div><dt class="text-text/60">Created By</dt><dd>{{ $task->creator?->name ?? '—' }}</dd></div>
                <div><dt class="text-text/60">Created Date</dt><dd>{{ $task->created_at?->toDateTimeString() ?? '—' }}</dd></div>
                <div><dt class="text-text/60">Last Modified By</dt><dd>{{ $task->updater?->name ?? '—' }}</dd></div>
                <div><dt class="text-text/60">Last Modified Date</dt><dd>{{ $task->updated_at?->toDateTimeString() ?? '—' }}</dd></div>
                <div><dt class="text-text/60">Completed At</dt><dd>{{ $task->completed_at?->toDateTimeString() ?? '—' }}</dd></div>
            </dl>
        </section>
    </div>
@endsection
