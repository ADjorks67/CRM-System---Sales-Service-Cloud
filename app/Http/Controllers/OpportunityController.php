<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\HandlesRecordOwnership;
use App\Http\Requests\BulkRecordActionRequest;
use App\Http\Requests\ChangeOwnerRequest;
use App\Http\Requests\CloneOpportunityRequest;
use App\Http\Requests\StoreOpportunityRequest;
use App\Http\Requests\UpdateOpportunityRequest;
use App\Models\Account;
use App\Models\Opportunity;
use App\Models\User;
use App\Services\BulkRecordActionService;
use App\Services\OwnershipHistoryService;
use App\Services\RecentRecordService;
use App\Services\StageService;
use App\Support\ListQuery;
use App\Support\PicklistOptions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class OpportunityController extends Controller
{
    use HandlesRecordOwnership;

    public function __construct(
        private readonly StageService $stageService,
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
        $this->authorize('viewAny', Opportunity::class);

        $query = Opportunity::query()
            ->visibleTo($request->user())
            ->with(['owner', 'account']);

        if ($request->query('view') === 'archived') {
            $query->whereNotNull('archived_at');
        } else {
            $query->notArchived();
        }

        $this->applySearch($query, $request->string('q')->toString(), [
            'name', 'stage', 'type', 'lead_source',
        ]);

        $applied = ListQuery::apply(
            $query,
            $request,
            allowedSorts: ['name', 'stage', 'amount', 'close_date', 'updated_at', 'created_at'],
            defaultSort: 'updated_at',
            defaultDirection: 'desc',
            defaultColumns: ['name', 'account', 'stage', 'amount', 'close_date', 'owner'],
            availableColumns: ['name', 'account', 'stage', 'amount', 'close_date', 'probability', 'lead_source', 'owner', 'updated_at'],
        );

        return view('opportunities.index', [
            'opportunities' => ListQuery::paginate($applied),
            'columns' => $applied['columns'],
            'sort' => $applied['sort'],
            'direction' => $applied['direction'],
            'owners' => User::query()->orderBy('name')->get(['id', 'name']),
            'stages' => PicklistOptions::options('opportunity_stage'),
            'viewMode' => $request->query('view') === 'archived' ? 'archived' : 'active',
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorize('create', Opportunity::class);

        return view('opportunities.create', $this->formLookups($request));
    }

    public function store(StoreOpportunityRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $stage = $validated['stage'];
        $data = $this->opportunityPayload($validated);
        $data['owner_id'] = $data['owner_id'] ?? $request->user()->id;

        unset($data['stage'], $data['probability']);

        $opportunity = DB::transaction(function () use ($data, $stage, $request): Opportunity {
            $opportunity = Opportunity::query()->create($data);
            $this->stageService->changeStage($opportunity, $stage, $request->user());

            return $opportunity->fresh();
        });

        if (($request->validated('save_action') ?? 'save') === 'save_new') {
            return redirect()
                ->route('opportunities.create')
                ->with('success', 'Opportunity created successfully.');
        }

        return redirect()
            ->route('opportunities.show', $opportunity)
            ->with('success', 'Opportunity created successfully.');
    }

    public function show(Request $request, Opportunity $opportunity): View
    {
        $this->authorize('view', $opportunity);

        $this->recentRecordService->recordView($request->user(), $opportunity);

        $opportunity->load(['owner', 'account', 'creator', 'updater', 'stageHistories.changedByUser']);

        return view('opportunities.show', [
            'opportunity' => $opportunity,
            'owners' => User::query()->orderBy('name')->get(['id', 'name']),
            'stages' => PicklistOptions::options('opportunity_stage'),
            'stageValues' => PicklistOptions::values('opportunity_stage'),
            'stageLabel' => PicklistOptions::options('opportunity_stage')[$opportunity->stage] ?? $opportunity->stage,
            'typeLabel' => PicklistOptions::options('opportunity_type')[$opportunity->type] ?? $opportunity->type,
            'sourceLabel' => PicklistOptions::options('lead_source')[$opportunity->lead_source] ?? $opportunity->lead_source,
        ]);
    }

    public function edit(Request $request, Opportunity $opportunity): View
    {
        $this->authorize('update', $opportunity);

        return view('opportunities.edit', array_merge($this->formLookups($request), [
            'opportunity' => $opportunity,
        ]));
    }

    public function update(UpdateOpportunityRequest $request, Opportunity $opportunity): RedirectResponse
    {
        $data = $this->opportunityPayload($request->validated());
        unset($data['probability']);

        $opportunity->fill($data);
        if ($request->filled('probability')) {
            $opportunity->probability = (int) $request->validated('probability');
        }
        $opportunity->recomputeExpectedRevenue();
        $opportunity->save();

        if (($request->validated('save_action') ?? 'save') === 'save_new') {
            return redirect()
                ->route('opportunities.create')
                ->with('success', 'Opportunity updated successfully.');
        }

        return redirect()
            ->route('opportunities.show', $opportunity)
            ->with('success', 'Opportunity updated successfully.');
    }

    public function destroy(Opportunity $opportunity): RedirectResponse
    {
        $this->authorize('delete', $opportunity);

        $opportunity->forceFill(['archived_at' => now()])->save();

        return redirect()
            ->route('opportunities.index')
            ->with('success', 'Opportunity archived successfully.');
    }

    public function archive(Opportunity $opportunity): RedirectResponse
    {
        $this->authorize('archive', $opportunity);

        $opportunity->forceFill(['archived_at' => now()])->save();

        return redirect()
            ->route('opportunities.index')
            ->with('success', 'Opportunity archived successfully.');
    }

    public function changeOwner(ChangeOwnerRequest $request, Opportunity $opportunity): RedirectResponse
    {
        return $this->changeRecordOwner($request, $opportunity, 'opportunities.show');
    }

    public function updateStage(Request $request, Opportunity $opportunity): RedirectResponse
    {
        $this->authorize('updateStage', $opportunity);

        $validated = $request->validate([
            'stage' => ['required', 'string', Rule::in(PicklistOptions::values('opportunity_stage'))],
        ]);

        $this->stageService->changeStage($opportunity, $validated['stage'], $request->user());

        return redirect()
            ->route('opportunities.show', $opportunity)
            ->with('success', 'Opportunity stage updated successfully.');
    }

    public function cloneForm(Opportunity $opportunity): View
    {
        $this->authorize('clone', $opportunity);

        return view('opportunities.clone', [
            'opportunity' => $opportunity,
        ]);
    }

    public function clone(CloneOpportunityRequest $request, Opportunity $opportunity): RedirectResponse
    {
        $clone = DB::transaction(function () use ($request, $opportunity): Opportunity {
            $attributes = $opportunity->only([
                'account_id', 'amount', 'close_date', 'type', 'lead_source', 'next_step', 'description',
            ]);
            $attributes['name'] = $opportunity->name.' (Copy)';
            $attributes['owner_id'] = $request->user()->id;

            $newOpportunity = Opportunity::query()->create($attributes);
            $this->stageService->changeStage($newOpportunity, 'qualification', $request->user());

            if ($request->boolean('include_related')) {
                // Related products/quotes arrive in later phases.
            }

            return $newOpportunity;
        });

        return redirect()
            ->route('opportunities.show', $clone)
            ->with('success', 'Opportunity cloned successfully.');
    }

    public function bulk(BulkRecordActionRequest $request): RedirectResponse
    {
        return $this->runBulkAction($request, Opportunity::class, 'opportunities.index');
    }

    /**
     * @return array<string, mixed>
     */
    private function formLookups(Request $request): array
    {
        return [
            'accounts' => Account::query()
                ->visibleTo($request->user())
                ->orderBy('name')
                ->get(['id', 'name']),
            'stages' => PicklistOptions::options('opportunity_stage'),
            'types' => PicklistOptions::options('opportunity_type'),
            'leadSources' => PicklistOptions::options('lead_source'),
            'owners' => User::query()->orderBy('name')->get(['id', 'name']),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function opportunityPayload(array $data): array
    {
        unset($data['save_action']);

        return $data;
    }
}
