<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\HandlesRecordOwnership;
use App\Http\Requests\BulkRecordActionRequest;
use App\Http\Requests\ChangeOwnerRequest;
use App\Http\Requests\StoreCrmCaseRequest;
use App\Http\Requests\UpdateCrmCaseRequest;
use App\Models\Account;
use App\Models\Contact;
use App\Models\CrmCase;
use App\Models\User;
use App\Services\BulkRecordActionService;
use App\Services\OwnershipHistoryService;
use App\Services\RecentRecordService;
use App\Support\ListQuery;
use App\Support\PicklistOptions;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CrmCaseController extends Controller
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
        $this->authorize('viewAny', CrmCase::class);

        $view = $request->string('view')->toString();
        if ($view === '') {
            $view = 'my_open';
        }

        $query = CrmCase::query()
            ->visibleTo($request->user())
            ->with(['owner', 'account', 'contact']);

        $this->applyListView($query, $request, $view);

        $this->applySearch($query, $request->string('q')->toString(), [
            'case_number', 'subject', 'status', 'priority', 'origin', 'web_email', 'web_name', 'web_company',
        ]);

        $applied = ListQuery::apply(
            $query,
            $request,
            allowedSorts: ['case_number', 'subject', 'status', 'priority', 'updated_at', 'created_at'],
            defaultSort: 'updated_at',
            defaultDirection: 'desc',
            defaultColumns: ['case_number', 'subject', 'status', 'priority', 'owner'],
            availableColumns: ['case_number', 'subject', 'status', 'priority', 'origin', 'account', 'contact', 'owner', 'updated_at'],
        );

        return view('cases.index', [
            'cases' => ListQuery::paginate($applied),
            'columns' => $applied['columns'],
            'sort' => $applied['sort'],
            'direction' => $applied['direction'],
            'view' => $view,
            'owners' => User::query()->orderBy('name')->get(['id', 'name']),
            'statuses' => PicklistOptions::options('case_status'),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', CrmCase::class);

        return view('cases.create', $this->formLookups());
    }

    public function store(StoreCrmCaseRequest $request): RedirectResponse
    {
        $data = $this->casePayload($request->validated());
        $data['owner_id'] = $data['owner_id'] ?? $request->user()->id;
        $data = $this->applyClosedTimestamp($data);

        $crmCase = CrmCase::query()->create($data);

        if (($request->validated('save_action') ?? 'save') === 'save_new') {
            return redirect()
                ->route('cases.create')
                ->with('success', 'Case created successfully.');
        }

        return redirect()
            ->route('cases.show', $crmCase)
            ->with('success', 'Case created successfully.');
    }

    public function show(Request $request, CrmCase $crmCase): View
    {
        $this->authorize('view', $crmCase);

        /** @var User $viewer */
        $viewer = $request->user();
        $this->recentRecordService->recordView($crmCase, $viewer);

        $crmCase->load(['owner', 'account', 'contact', 'creator', 'updater']);

        return view('cases.show', [
            'crmCase' => $crmCase,
            'owners' => User::query()->orderBy('name')->get(['id', 'name']),
            'statuses' => PicklistOptions::options('case_status'),
            'statusLabel' => PicklistOptions::options('case_status')[$crmCase->status] ?? $crmCase->status,
            'originLabel' => PicklistOptions::options('case_origin')[$crmCase->origin] ?? $crmCase->origin,
            'typeLabel' => PicklistOptions::options('case_type')[$crmCase->type] ?? $crmCase->type,
            'priorityLabel' => PicklistOptions::options('case_priority')[$crmCase->priority] ?? $crmCase->priority,
            'reasonLabel' => PicklistOptions::options('case_reason')[$crmCase->reason] ?? $crmCase->reason,
        ]);
    }

    public function edit(CrmCase $crmCase): View
    {
        $this->authorize('update', $crmCase);

        return view('cases.edit', array_merge($this->formLookups(), [
            'crmCase' => $crmCase,
        ]));
    }

    public function update(UpdateCrmCaseRequest $request, CrmCase $crmCase): RedirectResponse
    {
        $data = $this->applyClosedTimestamp($this->casePayload($request->validated()));
        $crmCase->update($data);

        if (($request->validated('save_action') ?? 'save') === 'save_new') {
            return redirect()
                ->route('cases.create')
                ->with('success', 'Case updated successfully.');
        }

        return redirect()
            ->route('cases.show', $crmCase)
            ->with('success', 'Case updated successfully.');
    }

    public function destroy(CrmCase $crmCase): RedirectResponse
    {
        $this->authorize('delete', $crmCase);

        $crmCase->delete();

        return redirect()
            ->route('cases.index')
            ->with('success', 'Case deleted successfully.');
    }

    public function changeOwner(ChangeOwnerRequest $request, CrmCase $crmCase): RedirectResponse
    {
        return $this->changeRecordOwner($request, $crmCase, 'cases.show');
    }

    public function changeStatus(Request $request, CrmCase $crmCase): RedirectResponse
    {
        $this->authorize('changeStatus', $crmCase);

        $validated = $request->validate([
            'status' => ['required', 'string', Rule::in(PicklistOptions::values('case_status'))],
        ]);

        $data = $this->applyClosedTimestamp(['status' => $validated['status']]);
        $crmCase->update($data);

        return redirect()
            ->route('cases.show', $crmCase)
            ->with('success', 'Case status updated successfully.');
    }

    public function reopen(CrmCase $crmCase): RedirectResponse
    {
        $this->authorize('reopen', $crmCase);

        $crmCase->update([
            'status' => 'working',
            'closed_at' => null,
        ]);

        return redirect()
            ->route('cases.show', $crmCase)
            ->with('success', 'Case reopened successfully.');
    }

    public function bulk(BulkRecordActionRequest $request): RedirectResponse
    {
        return $this->runBulkAction($request, CrmCase::class, 'cases.index');
    }

    /**
     * @param  Builder<CrmCase>  $query
     */
    private function applyListView($query, Request $request, string $view): void
    {
        match ($view) {
            'my_open' => $query
                ->where('owner_id', $request->user()->id)
                ->where('status', '!=', 'closed'),
            'all_open' => $query->where('status', '!=', 'closed'),
            'recently_closed' => $query
                ->where('status', 'closed')
                ->where('closed_at', '>=', now()->subDays(30)),
            default => $query
                ->where('owner_id', $request->user()->id)
                ->where('status', '!=', 'closed'),
        };
    }

    /**
     * @return array<string, mixed>
     */
    private function formLookups(): array
    {
        return [
            'statuses' => PicklistOptions::options('case_status'),
            'origins' => PicklistOptions::options('case_origin'),
            'types' => PicklistOptions::options('case_type'),
            'priorities' => PicklistOptions::options('case_priority'),
            'reasons' => PicklistOptions::options('case_reason'),
            'accounts' => Account::query()->orderBy('name')->get(['id', 'name']),
            'contacts' => Contact::query()->orderBy('last_name')->orderBy('first_name')->get(['id', 'first_name', 'last_name']),
            'owners' => User::query()->orderBy('name')->get(['id', 'name']),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function casePayload(array $data): array
    {
        unset($data['save_action']);

        return $data;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function applyClosedTimestamp(array $data): array
    {
        if (($data['status'] ?? null) === 'closed') {
            $data['closed_at'] = now();
        } elseif (array_key_exists('status', $data) && $data['status'] !== 'closed') {
            $data['closed_at'] = null;
        }

        return $data;
    }
}
