<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOpportunityRequest;
use App\Http\Requests\UpdateOpportunityRequest;
use App\Http\Resources\Api\V1\OpportunityResource;
use App\Models\Opportunity;
use App\Support\ApiFieldCatalog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class OpportunityController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Opportunity::class);

        $query = Opportunity::query()->visibleTo($request->user())->notArchived();
        ApiFieldCatalog::applyFilters($query, $request, 'opportunities');
        ApiFieldCatalog::applySort($query, $request, 'opportunities', 'name');

        return OpportunityResource::collection($query->paginate(ApiFieldCatalog::perPage($request)));
    }

    public function store(StoreOpportunityRequest $request): JsonResponse
    {
        $data = collect($request->validated())->except(['save_action'])->all();
        $data['owner_id'] = $data['owner_id'] ?? $request->user()->id;

        $opportunity = Opportunity::query()->create($data);
        $opportunity->recomputeExpectedRevenue();
        $opportunity->save();

        return (new OpportunityResource($opportunity))->response()->setStatusCode(201);
    }

    public function show(Opportunity $opportunity): OpportunityResource
    {
        $this->authorize('view', $opportunity);

        return new OpportunityResource($opportunity);
    }

    public function update(UpdateOpportunityRequest $request, Opportunity $opportunity): OpportunityResource
    {
        $data = collect($request->validated())->except(['save_action'])->all();
        $opportunity->update($data);
        $opportunity->recomputeExpectedRevenue();
        $opportunity->save();

        return new OpportunityResource($opportunity->fresh());
    }

    public function destroy(Opportunity $opportunity): JsonResponse
    {
        $this->authorize('delete', $opportunity);
        $opportunity->delete();

        return response()->json(null, 204);
    }
}
