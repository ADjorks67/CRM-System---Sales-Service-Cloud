@extends('layouts.app')

@section('title', 'Tasks — '.config('app.name'))

@section('content')
    <div class="mb-4 flex flex-wrap items-end justify-between gap-3">
        <div>
            <h1>Tasks</h1>
            <p class="text-sm text-text/70">Open activities (FR-TASK-001).</p>
        </div>
        @can('create', App\Models\Task::class)
            <a href="{{ route('tasks.create') }}" class="inline-flex min-h-11 items-center rounded bg-secondary px-4 py-2 text-sm font-semibold text-white no-underline hover:bg-secondary/90">
                New Task
            </a>
        @endcan
    </div>

    <nav class="mb-4 flex flex-wrap gap-2 text-sm" aria-label="Task list views">
        @foreach (['open' => 'Open', 'today' => 'Today', 'overdue' => 'Overdue', 'completed' => 'Completed'] as $key => $label)
            <a
                href="{{ route('tasks.index', ['view' => $key]) }}"
                @class([
                    'inline-flex min-h-11 items-center rounded px-4 py-2 no-underline',
                    'bg-secondary text-white font-semibold' => $view === $key,
                    'border border-black/20 bg-card text-text' => $view !== $key,
                ])
            >{{ $label }}</a>
        @endforeach
    </nav>

    <form method="get" action="{{ route('tasks.index') }}" class="mb-4 flex flex-wrap gap-2">
        <input type="hidden" name="view" value="{{ $view }}">
        <label for="task-search" class="sr-only">Search tasks</label>
        <input id="task-search" type="search" name="q" value="{{ request('q') }}" placeholder="Search tasks…" class="min-h-11 min-w-[14rem] flex-1 rounded border border-black/20 bg-card px-3 py-2 text-sm">
        <button type="submit" class="inline-flex min-h-11 items-center rounded border border-black/20 bg-card px-4 py-2 text-sm">Search</button>
    </form>

    @php
        $columnMap = [
            'subject' => 'Subject',
            'related' => 'Related To',
            'contact' => 'Name',
            'due_date' => 'Due Date',
            'status' => 'Status',
            'priority' => 'Priority',
            'owner' => 'Assigned To',
            'updated_at' => 'Last Modified',
        ];
        $visible = collect($columns)
            ->filter(fn ($key) => isset($columnMap[$key]))
            ->mapWithKeys(fn ($key) => [$key => $columnMap[$key]])
            ->all();
        $visible = ['select' => ''] + $visible + ['actions' => 'Actions'];
        $statusLabels = \App\Support\PicklistOptions::options('task_status');
        $priorityLabels = \App\Support\PicklistOptions::options('task_priority');
    @endphp

    <form method="post" action="{{ route('tasks.bulk') }}">
        @csrf
        <div class="mb-3 flex flex-wrap items-end gap-2">
            <x-form-field name="action" label="Bulk action" :options="['change_owner' => 'Change Owner', 'delete' => 'Delete']" class="min-w-[12rem]" />
            <x-form-field name="owner_id" label="New owner" :options="$owners->mapWithKeys(fn ($u) => [$u->id => $u->name])->all()" class="min-w-[12rem]" />
            <button type="submit" class="inline-flex min-h-11 items-center rounded border border-black/20 bg-card px-4 py-2 text-sm" onclick="return confirm('Apply bulk action to selected tasks?')">Apply</button>
        </div>

        <x-data-table
            :columns="$visible"
            :has-rows="$tasks->count() > 0"
            :paginator="$tasks"
            :sort="$sort"
            :direction="$direction"
            :sortable="['subject', 'status', 'priority', 'due_date', 'updated_at']"
            empty="No tasks yet."
        >
            @foreach ($tasks as $index => $task)
                <tr @class(['border-t border-black/10', 'bg-page/60' => $index % 2 === 1, 'hover:bg-secondary/5'])>
                    <td class="px-3 py-3">
                        <input type="checkbox" name="ids[]" value="{{ $task->id }}" class="size-4 rounded border-black/20" aria-label="Select {{ $task->subject }}">
                    </td>
                    @foreach ($columns as $key)
                        @continue(! isset($columnMap[$key]))
                        <td class="px-3 py-3">
                            @switch($key)
                                @case('subject')
                                    <a href="{{ route('tasks.show', $task) }}" class="font-medium">{{ $task->subject }}</a>
                                    @break
                                @case('related')
                                    @if ($task->related)
                                        {{ ucfirst($task->related_type) }}:
                                        {{ method_exists($task->related, 'displayName') ? $task->related->displayName() : $task->related_id }}
                                    @else
                                        —
                                    @endif
                                    @break
                                @case('contact')
                                    {{ $task->contact?->displayName() ?? '—' }}
                                    @break
                                @case('due_date')
                                    <span @class(['text-error font-medium' => $task->isOverdue()])>
                                        {{ $task->due_date?->toDateString() ?? '—' }}
                                    </span>
                                    @break
                                @case('status')
                                    {{ $statusLabels[$task->status] ?? $task->status }}
                                    @break
                                @case('priority')
                                    {{ $priorityLabels[$task->priority] ?? $task->priority }}
                                    @break
                                @case('owner')
                                    {{ $task->owner?->name ?? '—' }}
                                    @break
                                @case('updated_at')
                                    {{ $task->updated_at?->toDateTimeString() ?? '—' }}
                                    @break
                            @endswitch
                        </td>
                    @endforeach
                    <td class="px-3 py-3">
                        <div class="flex flex-wrap gap-2">
                            @can('complete', $task)
                                @unless ($task->isCompleted())
                                    <form method="post" action="{{ route('tasks.complete', $task) }}">
                                        @csrf
                                        <button type="submit" class="text-sm text-success">Complete</button>
                                    </form>
                                @endunless
                            @endcan
                            @can('update', $task)
                                <a href="{{ route('tasks.edit', $task) }}" class="text-sm">Edit</a>
                            @endcan
                        </div>
                    </td>
                </tr>
            @endforeach
        </x-data-table>
    </form>
@endsection
