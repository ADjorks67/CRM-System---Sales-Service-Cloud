<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\HandlesRecordOwnership;
use App\Http\Requests\BulkRecordActionRequest;
use App\Http\Requests\ChangeOwnerRequest;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Models\Account;
use App\Models\Contact;
use App\Models\CrmCase;
use App\Models\Lead;
use App\Models\Opportunity;
use App\Models\Task;
use App\Models\User;
use App\Notifications\TaskAssignedNotification;
use App\Services\BulkRecordActionService;
use App\Services\OwnershipHistoryService;
use App\Services\RecentRecordService;
use App\Support\ListQuery;
use App\Support\PicklistOptions;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\View\View;

class TaskController extends Controller
{
    use HandlesRecordOwnership;

    public function __construct(
        private readonly OwnershipHistoryService $ownershipHistoryService,
        private readonly BulkRecordActionService $bulkRecordActionService,
        private readonly RecentRecordService $recentRecordService,
    ) {}

    protected function ownershipHistory(): OwnershipHistoryService
    {
        return $this->ownershipHistoryService;
    }

    protected function bulkRecordActions(): BulkRecordActionService
    {
        return $this->bulkRecordActionService;
    }

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Task::class);

        $view = $request->string('view')->toString();
        if ($view === '') {
            $view = 'open';
        }

        $query = Task::query()
            ->visibleTo($request->user())
            ->with(['owner', 'related', 'contact']);

        $this->applyListView($query, $view);

        $this->applySearch($query, $request->string('q')->toString(), [
            'subject', 'status', 'priority', 'comments',
        ]);

        $applied = ListQuery::apply(
            $query,
            $request,
            allowedSorts: ['subject', 'status', 'priority', 'due_date', 'updated_at', 'created_at'],
            defaultSort: 'updated_at',
            defaultDirection: 'desc',
            defaultColumns: ['subject', 'related', 'due_date', 'status', 'priority', 'owner'],
            availableColumns: ['subject', 'related', 'contact', 'due_date', 'status', 'priority', 'owner', 'updated_at'],
        );

        return view('tasks.index', [
            'tasks' => ListQuery::paginate($applied),
            'columns' => $applied['columns'],
            'sort' => $applied['sort'],
            'direction' => $applied['direction'],
            'view' => $view,
            'owners' => User::query()->orderBy('name')->get(['id', 'name']),
            'statuses' => PicklistOptions::options('task_status'),
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorize('create', Task::class);

        return view('tasks.create', array_merge($this->formLookups(), [
            'prefill' => [
                'related_type' => $request->string('related_type')->toString() ?: null,
                'related_id' => $request->integer('related_id') ?: null,
            ],
        ]));
    }

    public function store(StoreTaskRequest $request): RedirectResponse
    {
        $data = $this->taskPayload($request->validated());
        $data['owner_id'] = $data['owner_id'] ?? $request->user()->id;
        $data = $this->applyCompletionTimestamp($data);

        $task = Task::query()->create($data);
        $this->notifyAssigneeIfNeeded($task, $request->user());

        if (($request->validated('save_action') ?? 'save') === 'save_new') {
            return redirect()
                ->route('tasks.create')
                ->with('success', 'Task created successfully.');
        }

        return redirect()
            ->route('tasks.show', $task)
            ->with('success', 'Task created successfully.');
    }

    public function show(Request $request, Task $task): View
    {
        $this->authorize('view', $task);

        $task->load(['owner', 'related', 'contact', 'creator', 'updater']);
        $this->recentRecordService->recordView($request->user(), $task);

        return view('tasks.show', array_merge($this->formLookups(), [
            'task' => $task,
            'statusLabel' => PicklistOptions::options('task_status')[$task->status] ?? $task->status,
            'priorityLabel' => PicklistOptions::options('task_priority')[$task->priority] ?? $task->priority,
            'relatedLabel' => $this->relatedLabel($task),
        ]));
    }

    public function edit(Task $task): View
    {
        $this->authorize('update', $task);

        return view('tasks.edit', array_merge($this->formLookups(), [
            'task' => $task,
        ]));
    }

    public function update(UpdateTaskRequest $request, Task $task): RedirectResponse
    {
        $previousOwnerId = $task->owner_id;
        $data = $this->applyCompletionTimestamp($this->taskPayload($request->validated()));
        $task->update($data);

        if (isset($data['owner_id']) && (int) $data['owner_id'] !== (int) $previousOwnerId) {
            $this->notifyAssigneeIfNeeded($task->fresh(), $request->user());
        }

        if (($request->validated('save_action') ?? 'save') === 'save_new') {
            return redirect()
                ->route('tasks.create')
                ->with('success', 'Task updated successfully.');
        }

        return redirect()
            ->route('tasks.show', $task)
            ->with('success', 'Task updated successfully.');
    }

    public function destroy(Task $task): RedirectResponse
    {
        $this->authorize('delete', $task);
        $task->delete();

        return redirect()
            ->route('tasks.index')
            ->with('success', 'Task deleted successfully.');
    }

    public function complete(Task $task): RedirectResponse
    {
        $this->authorize('complete', $task);

        $task->forceFill([
            'status' => 'completed',
            'completed_at' => now(),
        ])->save();

        return redirect()
            ->back()
            ->with('success', 'Task marked completed.');
    }

    public function changeOwner(ChangeOwnerRequest $request, Task $task): RedirectResponse
    {
        return $this->changeRecordOwner($request, $task, 'tasks.show');
    }

    public function bulk(BulkRecordActionRequest $request): RedirectResponse
    {
        return $this->runBulkAction($request, Task::class, 'tasks.index');
    }

    /**
     * @return array<string, mixed>
     */
    private function formLookups(): array
    {
        return [
            'statuses' => PicklistOptions::options('task_status'),
            'priorities' => PicklistOptions::options('task_priority'),
            'relatedTypes' => [
                'account' => 'Account',
                'contact' => 'Contact',
                'lead' => 'Lead',
                'opportunity' => 'Opportunity',
                'case' => 'Case',
            ],
            'owners' => User::query()->orderBy('name')->get(['id', 'name']),
            'contacts' => Contact::query()->orderBy('last_name')->limit(200)->get(),
            'relatedOptions' => $this->relatedOptions(),
        ];
    }

    /**
     * @return array<string, array<int, string>>
     */
    private function relatedOptions(): array
    {
        return [
            'account' => Account::query()->orderBy('name')->limit(100)->pluck('name', 'id')->all(),
            'contact' => Contact::query()->orderBy('last_name')->limit(100)->get()
                ->mapWithKeys(fn (Contact $c) => [$c->id => $c->displayName()])->all(),
            'lead' => Lead::query()->orderBy('last_name')->limit(100)->get()
                ->mapWithKeys(fn (Lead $l) => [$l->id => $l->displayName()])->all(),
            'opportunity' => Opportunity::query()->notArchived()->orderBy('name')->limit(100)->pluck('name', 'id')->all(),
            'case' => CrmCase::query()->orderByDesc('id')->limit(100)->get()
                ->mapWithKeys(fn (CrmCase $c) => [$c->id => $c->case_number.' — '.$c->displayName()])->all(),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function taskPayload(array $data): array
    {
        unset($data['save_action']);

        if (empty($data['related_type']) || empty($data['related_id'])) {
            $data['related_type'] = null;
            $data['related_id'] = null;
        }

        if (! ($data['reminder_set'] ?? false)) {
            $data['reminder_at'] = null;
        }

        return $data;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function applyCompletionTimestamp(array $data): array
    {
        if (($data['status'] ?? null) === 'completed') {
            $data['completed_at'] = $data['completed_at'] ?? now();
        } else {
            $data['completed_at'] = null;
        }

        return $data;
    }

    private function applyListView(Builder $query, string $view): void
    {
        match ($view) {
            'completed' => $query->completed(),
            'today' => $query->dueToday(),
            'overdue' => $query->overdue(),
            default => $query->open(),
        };
    }

    private function relatedLabel(Task $task): string
    {
        if ($task->related === null) {
            return '—';
        }

        $name = method_exists($task->related, 'displayName')
            ? $task->related->displayName()
            : (string) $task->related->getKey();

        return ucfirst((string) $task->related_type).': '.$name;
    }

    private function notifyAssigneeIfNeeded(Task $task, User $actor): void
    {
        $assignee = $task->owner;
        if ($assignee === null || (int) $assignee->id === (int) $actor->id) {
            return;
        }

        Notification::send($assignee, new TaskAssignedNotification($task, $actor));
    }
}
