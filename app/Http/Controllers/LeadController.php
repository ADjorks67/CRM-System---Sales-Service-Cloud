<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\HandlesRecordOwnership;
use App\Http\Requests\BulkRecordActionRequest;
use App\Http\Requests\ChangeOwnerRequest;
use App\Http\Requests\StoreLeadRequest;
use App\Http\Requests\UpdateLeadRequest;
use App\Models\Lead;
use App\Models\User;
use App\Services\BulkRecordActionService;
use App\Services\OwnershipHistoryService;
use App\Support\ListQuery;
use App\Support\PicklistOptions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class LeadController extends Controller
{
    use HandlesRecordOwnership;

    public function __construct(
        private readonly OwnershipHistoryService $ownershipHistoryService,
        private readonly BulkRecordActionService $bulkRecordActionService,
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
        $this->authorize('viewAny', Lead::class);

        $query = Lead::query()
            ->visibleTo($request->user())
            ->with('owner');

        $this->applySearch($query, $request->string('q')->toString(), [
            'first_name', 'last_name', 'company', 'email', 'phone', 'status',
        ]);

        $applied = ListQuery::apply(
            $query,
            $request,
            allowedSorts: ['last_name', 'company', 'status', 'email', 'updated_at', 'created_at'],
            defaultSort: 'updated_at',
            defaultDirection: 'desc',
            defaultColumns: ['name', 'company', 'status', 'email', 'phone', 'owner'],
            availableColumns: ['name', 'company', 'status', 'email', 'phone', 'lead_source', 'rating', 'owner', 'updated_at'],
        );

        return view('leads.index', [
            'leads' => ListQuery::paginate($applied),
            'columns' => $applied['columns'],
            'sort' => $applied['sort'],
            'direction' => $applied['direction'],
            'owners' => User::query()->orderBy('name')->get(['id', 'name']),
            'statuses' => PicklistOptions::editableLeadStatuses(),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Lead::class);

        return view('leads.create', $this->formLookups());
    }

    public function store(StoreLeadRequest $request): RedirectResponse
    {
        $data = $this->leadPayload($request->validated());
        $data['owner_id'] = $data['owner_id'] ?? $request->user()->id;

        $lead = Lead::query()->create($data);

        if (($request->validated('save_action') ?? 'save') === 'save_new') {
            return redirect()
                ->route('leads.create')
                ->with('success', 'Lead created successfully.');
        }

        return redirect()
            ->route('leads.show', $lead)
            ->with('success', 'Lead created successfully.');
    }

    public function show(Lead $lead): View
    {
        $this->authorize('view', $lead);

        $lead->load(['owner', 'creator', 'updater']);

        return view('leads.show', [
            'lead' => $lead,
            'owners' => User::query()->orderBy('name')->get(['id', 'name']),
            'statuses' => PicklistOptions::editableLeadStatuses(),
            'statusLabel' => $lead->status?->label() ?? '—',
            'sourceLabel' => PicklistOptions::options('lead_source')[$lead->lead_source] ?? $lead->lead_source,
            'ratingLabel' => PicklistOptions::options('rating')[$lead->rating] ?? $lead->rating,
            'industryLabel' => PicklistOptions::options('industry')[$lead->industry] ?? $lead->industry,
            'salutationLabel' => PicklistOptions::options('salutation')[$lead->salutation] ?? $lead->salutation,
        ]);
    }

    public function edit(Lead $lead): View
    {
        $this->authorize('update', $lead);

        return view('leads.edit', array_merge($this->formLookups(), [
            'lead' => $lead,
        ]));
    }

    public function update(UpdateLeadRequest $request, Lead $lead): RedirectResponse
    {
        $lead->update($this->leadPayload($request->validated()));

        if (($request->validated('save_action') ?? 'save') === 'save_new') {
            return redirect()
                ->route('leads.create')
                ->with('success', 'Lead updated successfully.');
        }

        return redirect()
            ->route('leads.show', $lead)
            ->with('success', 'Lead updated successfully.');
    }

    public function destroy(Lead $lead): RedirectResponse
    {
        $this->authorize('delete', $lead);

        $lead->delete();

        return redirect()
            ->route('leads.index')
            ->with('success', 'Lead deleted successfully.');
    }

    public function changeOwner(ChangeOwnerRequest $request, Lead $lead): RedirectResponse
    {
        return $this->changeRecordOwner($request, $lead, 'leads.show');
    }

    public function changeStatus(Request $request, Lead $lead): RedirectResponse
    {
        $this->authorize('changeStatus', $lead);

        $validated = $request->validate([
            'status' => ['required', 'string', Rule::in(array_keys(PicklistOptions::editableLeadStatuses()))],
        ]);

        $lead->update(['status' => $validated['status']]);

        return redirect()
            ->route('leads.show', $lead)
            ->with('success', 'Lead status updated successfully.');
    }

    public function bulk(BulkRecordActionRequest $request): RedirectResponse
    {
        return $this->runBulkAction($request, Lead::class, 'leads.index');
    }

    /**
     * @return array<string, mixed>
     */
    private function formLookups(): array
    {
        return [
            'salutations' => PicklistOptions::options('salutation'),
            'statuses' => PicklistOptions::editableLeadStatuses(),
            'leadSources' => PicklistOptions::options('lead_source'),
            'ratings' => PicklistOptions::options('rating'),
            'industries' => PicklistOptions::options('industry'),
            'owners' => User::query()->orderBy('name')->get(['id', 'name']),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function leadPayload(array $data): array
    {
        unset($data['save_action']);

        return $data;
    }
}
