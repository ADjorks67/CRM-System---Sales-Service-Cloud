@extends('layouts.app')

@section('title', $task->subject.' — '.config('app.name'))

@section('content')
    <div class="mb-4 flex flex-wrap items-start justify-between gap-3">
        <div>
            <x-ui.back :href="route('tasks.index')" label="Back to Tasks" class="mb-2" />
            <h1>{{ $task->subject }}</h1>
            <p class="text-sm text-muted">
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
                        <button type="submit" class="crm-btn-primary">Mark Complete</button>
                    </form>
                @endunless
            @endcan
            @can('update', $task)
                <a href="{{ route('tasks.edit', $task) }}" class="crm-btn-secondary">Edit</a>
            @endcan
            @can('delete', $task)
                <form method="post" action="{{ route('tasks.destroy', $task) }}" onsubmit="return confirm('Delete this task?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="crm-btn-danger">Delete</button>
                </form>
            @endcan
        </div>
    </div>

    @can('changeOwner', $task)
        <form method="post" action="{{ route('tasks.change-owner', $task) }}" class="mb-6 flex flex-wrap items-end gap-2 crm-card p-4 sm:p-5">
            @csrf
            <x-form-field name="owner_id" label="Change Assigned To" :value="$task->owner_id" :options="$owners->mapWithKeys(fn ($u) => [$u->id => $u->name])->all()" class="min-w-[14rem]" required />
            <label class="flex min-h-11 items-center gap-2 text-sm">
                <input type="checkbox" name="notify_new_owner" value="1" class="size-4 rounded border-border" checked>
                Notify new owner
            </label>
            <button type="submit" class="crm-btn-secondary">Change Owner</button>
        </form>
    @endcan

    <div class="grid gap-4 lg:grid-cols-2">
        <section class="crm-card p-4 sm:p-5">
            <h2 class="mb-3 text-base font-semibold text-primary">Task Information</h2>
            <dl class="grid gap-2 text-sm sm:grid-cols-2">
                <div><dt class="text-muted">Status</dt><dd>{{ $statusLabel }}</dd></div>
                <div><dt class="text-muted">Priority</dt><dd>{{ $priorityLabel }}</dd></div>
                <div><dt class="text-muted">Due Date</dt><dd>{{ $task->due_date?->toDateString() ?? '—' }}</dd></div>
                <div><dt class="text-muted">Related To</dt><dd>{{ $relatedLabel }}</dd></div>
                <div><dt class="text-muted">Name</dt><dd>{{ $task->contact?->displayName() ?? '—' }}</dd></div>
                <div><dt class="text-muted">Reminder</dt><dd>{{ $task->reminder_set ? ($task->reminder_at?->toDateTimeString() ?? 'Set') : '—' }}</dd></div>
            </dl>
        </section>
        <section class="crm-card p-4 sm:p-5">
            <h2 class="mb-3 text-base font-semibold text-primary">Comments</h2>
            <p class="whitespace-pre-wrap text-sm">{{ $task->comments ?: '—' }}</p>
        </section>
        <section class="crm-card p-4 sm:p-5 lg:col-span-2">
            <h2 class="mb-3 text-base font-semibold text-primary">System Information</h2>
            <dl class="grid gap-2 text-sm sm:grid-cols-2">
                <div><dt class="text-muted">Assigned To</dt><dd>{{ $task->owner?->name ?? '—' }}</dd></div>
                <div><dt class="text-muted">Created By</dt><dd>{{ $task->creator?->name ?? '—' }}</dd></div>
                <div><dt class="text-muted">Created Date</dt><dd>{{ $task->created_at?->toDateTimeString() ?? '—' }}</dd></div>
                <div><dt class="text-muted">Last Modified By</dt><dd>{{ $task->updater?->name ?? '—' }}</dd></div>
                <div><dt class="text-muted">Last Modified Date</dt><dd>{{ $task->updated_at?->toDateTimeString() ?? '—' }}</dd></div>
                <div><dt class="text-muted">Completed At</dt><dd>{{ $task->completed_at?->toDateTimeString() ?? '—' }}</dd></div>
            </dl>
        </section>
    </div>
@endsection
