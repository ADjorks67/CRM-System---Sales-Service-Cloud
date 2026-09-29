<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\HandlesRecordOwnership;
use App\Http\Requests\BulkRecordActionRequest;
use App\Http\Requests\ChangeOwnerRequest;
use App\Http\Requests\StoreAccountRequest;
use App\Http\Requests\UpdateAccountRequest;
use App\Models\Account;
use App\Models\User;
use App\Services\BulkRecordActionService;
use App\Services\OwnershipHistoryService;
use App\Support\ListQuery;
use App\Support\PicklistOptions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AccountController extends Controller
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
        $this->authorize('viewAny', Account::class);

        $query = Account::query()
            ->visibleTo($request->user())
            ->with('owner');

        $this->applySearch($query, $request->string('q')->toString(), ['name', 'phone', 'website', 'industry', 'type']);

        $applied = ListQuery::apply(
            $query,
            $request,
            allowedSorts: ['name', 'type', 'industry', 'updated_at', 'created_at'],
            defaultSort: 'updated_at',
            defaultDirection: 'desc',
            defaultColumns: ['name', 'type', 'industry', 'phone', 'owner'],
            availableColumns: ['name', 'type', 'industry', 'phone', 'website', 'owner', 'updated_at'],
        );

        return view('accounts.index', [
            'accounts' => ListQuery::paginate($applied),
            'columns' => $applied['columns'],
            'sort' => $applied['sort'],
            'direction' => $applied['direction'],
            'owners' => User::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorize('create', Account::class);

        return view('accounts.create', $this->formLookups($request));
    }

    public function store(StoreAccountRequest $request): RedirectResponse
    {
        $data = $this->accountPayload($request->validated());
        $data['owner_id'] = $data['owner_id'] ?? $request->user()->id;

        $account = Account::query()->create($data);

        if (($request->validated('save_action') ?? 'save') === 'save_new') {
            return redirect()
                ->route('accounts.create')
                ->with('success', 'Account created successfully.');
        }

        return redirect()
            ->route('accounts.show', $account)
            ->with('success', 'Account created successfully.');
    }

    public function show(Account $account): View
    {
        $this->authorize('view', $account);

        $account->load(['owner', 'parentAccount', 'creator', 'updater', 'contacts.owner']);

        return view('accounts.show', [
            'account' => $account,
            'owners' => User::query()->orderBy('name')->get(['id', 'name']),
            'typeLabel' => PicklistOptions::options('account_type')[$account->type] ?? $account->type,
            'industryLabel' => PicklistOptions::options('industry')[$account->industry] ?? $account->industry,
        ]);
    }

    public function edit(Request $request, Account $account): View
    {
        $this->authorize('update', $account);

        return view('accounts.edit', array_merge($this->formLookups($request, $account), [
            'account' => $account,
        ]));
    }

    public function update(UpdateAccountRequest $request, Account $account): RedirectResponse
    {
        $account->update($this->accountPayload($request->validated()));

        if (($request->validated('save_action') ?? 'save') === 'save_new') {
            return redirect()
                ->route('accounts.create')
                ->with('success', 'Account updated successfully.');
        }

        return redirect()
            ->route('accounts.show', $account)
            ->with('success', 'Account updated successfully.');
    }

    public function destroy(Account $account): RedirectResponse
    {
        $this->authorize('delete', $account);

        $account->delete();

        return redirect()
            ->route('accounts.index')
            ->with('success', 'Account deleted successfully.');
    }

    public function changeOwner(ChangeOwnerRequest $request, Account $account): RedirectResponse
    {
        return $this->changeRecordOwner($request, $account, 'accounts.show');
    }

    public function bulk(BulkRecordActionRequest $request): RedirectResponse
    {
        return $this->runBulkAction($request, Account::class, 'accounts.index');
    }

    /**
     * @return array<string, mixed>
     */
    private function formLookups(Request $request, ?Account $except = null): array
    {
        $parentQuery = Account::query()
            ->visibleTo($request->user())
            ->orderBy('name');

        if ($except !== null) {
            $parentQuery->whereKeyNot($except->id);
        }

        return [
            'parentAccounts' => $parentQuery->get(['id', 'name']),
            'types' => PicklistOptions::options('account_type'),
            'industries' => PicklistOptions::options('industry'),
            'owners' => User::query()->orderBy('name')->get(['id', 'name']),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function accountPayload(array $data): array
    {
        unset($data['copy_billing_to_shipping'], $data['save_action']);

        return $data;
    }
}
