<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\HandlesRecordOwnership;
use App\Http\Requests\BulkRecordActionRequest;
use App\Http\Requests\ChangeOwnerRequest;
use App\Http\Requests\StoreContactRequest;
use App\Http\Requests\UpdateContactRequest;
use App\Models\Account;
use App\Models\Contact;
use App\Models\User;
use App\Services\BulkRecordActionService;
use App\Services\OwnershipHistoryService;
use App\Support\ListQuery;
use App\Support\PicklistOptions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ContactController extends Controller
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
        $this->authorize('viewAny', Contact::class);

        $query = Contact::query()
            ->visibleTo($request->user())
            ->with(['owner', 'account']);

        $this->applySearch($query, $request->string('q')->toString(), [
            'first_name', 'last_name', 'email', 'phone', 'title',
        ]);

        $applied = ListQuery::apply(
            $query,
            $request,
            allowedSorts: ['last_name', 'first_name', 'email', 'updated_at', 'created_at'],
            defaultSort: 'updated_at',
            defaultDirection: 'desc',
            defaultColumns: ['name', 'account', 'title', 'email', 'phone', 'owner'],
            availableColumns: ['name', 'account', 'title', 'email', 'phone', 'owner', 'updated_at'],
        );

        return view('contacts.index', [
            'contacts' => ListQuery::paginate($applied),
            'columns' => $applied['columns'],
            'sort' => $applied['sort'],
            'direction' => $applied['direction'],
            'owners' => User::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorize('create', Contact::class);

        return view('contacts.create', $this->formLookups($request));
    }

    public function store(StoreContactRequest $request): RedirectResponse
    {
        $data = $this->contactPayload($request->validated());
        $data['owner_id'] = $data['owner_id'] ?? $request->user()->id;

        $contact = Contact::query()->create($data);

        if (($request->validated('save_action') ?? 'save') === 'save_new') {
            return redirect()
                ->route('contacts.create')
                ->with('success', 'Contact created successfully.');
        }

        return redirect()
            ->route('contacts.show', $contact)
            ->with('success', 'Contact created successfully.');
    }

    public function show(Contact $contact): View
    {
        $this->authorize('view', $contact);

        $contact->load(['owner', 'account', 'reportsTo', 'creator', 'updater']);

        return view('contacts.show', [
            'contact' => $contact,
            'owners' => User::query()->orderBy('name')->get(['id', 'name']),
            'salutationLabel' => PicklistOptions::options('salutation')[$contact->salutation] ?? $contact->salutation,
            'leadSourceLabel' => PicklistOptions::options('lead_source')[$contact->lead_source] ?? $contact->lead_source,
        ]);
    }

    public function edit(Request $request, Contact $contact): View
    {
        $this->authorize('update', $contact);

        return view('contacts.edit', array_merge($this->formLookups($request, $contact), [
            'contact' => $contact,
        ]));
    }

    public function update(UpdateContactRequest $request, Contact $contact): RedirectResponse
    {
        $contact->update($this->contactPayload($request->validated()));

        if (($request->validated('save_action') ?? 'save') === 'save_new') {
            return redirect()
                ->route('contacts.create')
                ->with('success', 'Contact updated successfully.');
        }

        return redirect()
            ->route('contacts.show', $contact)
            ->with('success', 'Contact updated successfully.');
    }

    public function destroy(Contact $contact): RedirectResponse
    {
        $this->authorize('delete', $contact);

        $contact->delete();

        return redirect()
            ->route('contacts.index')
            ->with('success', 'Contact deleted successfully.');
    }

    public function changeOwner(ChangeOwnerRequest $request, Contact $contact): RedirectResponse
    {
        return $this->changeRecordOwner($request, $contact, 'contacts.show');
    }

    public function bulk(BulkRecordActionRequest $request): RedirectResponse
    {
        return $this->runBulkAction($request, Contact::class, 'contacts.index');
    }

    /**
     * @return array<string, mixed>
     */
    private function formLookups(Request $request, ?Contact $except = null): array
    {
        $reportsQuery = Contact::query()
            ->visibleTo($request->user())
            ->orderBy('last_name')
            ->orderBy('first_name');

        if ($except !== null) {
            $reportsQuery->whereKeyNot($except->id);
        }

        return [
            'accounts' => Account::query()
                ->visibleTo($request->user())
                ->orderBy('name')
                ->get(['id', 'name']),
            'reportsToContacts' => $reportsQuery->get(['id', 'first_name', 'last_name']),
            'salutations' => PicklistOptions::options('salutation'),
            'leadSources' => PicklistOptions::options('lead_source'),
            'owners' => User::query()->orderBy('name')->get(['id', 'name']),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function contactPayload(array $data): array
    {
        unset($data['save_action']);

        return $data;
    }
}
