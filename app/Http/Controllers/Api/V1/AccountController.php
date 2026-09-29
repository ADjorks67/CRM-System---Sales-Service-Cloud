<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAccountRequest;
use App\Http\Requests\UpdateAccountRequest;
use App\Http\Resources\Api\V1\AccountResource;
use App\Models\Account;
use App\Support\ApiFieldCatalog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class AccountController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Account::class);

        $query = Account::query()->visibleTo($request->user());
        ApiFieldCatalog::applyFilters($query, $request, 'accounts');
        ApiFieldCatalog::applySort($query, $request, 'accounts', 'name');

        return AccountResource::collection(
            $query->paginate(ApiFieldCatalog::perPage($request))
        );
    }

    public function store(StoreAccountRequest $request): JsonResponse
    {
        $data = collect($request->validated())
            ->except(['copy_billing_to_shipping', 'save_action'])
            ->all();
        $data['owner_id'] = $data['owner_id'] ?? $request->user()->id;

        $account = Account::query()->create($data);

        return (new AccountResource($account))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Account $account): AccountResource
    {
        $this->authorize('view', $account);

        return new AccountResource($account);
    }

    public function update(UpdateAccountRequest $request, Account $account): AccountResource
    {
        $data = collect($request->validated())
            ->except(['copy_billing_to_shipping', 'save_action'])
            ->all();
        $account->update($data);

        return new AccountResource($account->fresh());
    }

    public function destroy(Account $account): JsonResponse
    {
        $this->authorize('delete', $account);
        $account->delete();

        return response()->json(null, 204);
    }
}
