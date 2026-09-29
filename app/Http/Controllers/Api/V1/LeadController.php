<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLeadRequest;
use App\Http\Requests\UpdateLeadRequest;
use App\Http\Resources\Api\V1\LeadResource;
use App\Models\Lead;
use App\Support\ApiFieldCatalog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class LeadController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Lead::class);

        $query = Lead::query()->visibleTo($request->user());
        ApiFieldCatalog::applyFilters($query, $request, 'leads');
        ApiFieldCatalog::applySort($query, $request, 'leads', 'last_name');

        return LeadResource::collection($query->paginate(ApiFieldCatalog::perPage($request)));
    }

    public function store(StoreLeadRequest $request): JsonResponse
    {
        $data = collect($request->validated())->except(['save_action'])->all();
        $data['owner_id'] = $data['owner_id'] ?? $request->user()->id;

        $lead = Lead::query()->create($data);

        return (new LeadResource($lead))->response()->setStatusCode(201);
    }

    public function show(Lead $lead): LeadResource
    {
        $this->authorize('view', $lead);

        return new LeadResource($lead);
    }

    public function update(UpdateLeadRequest $request, Lead $lead): LeadResource
    {
        $data = collect($request->validated())->except(['save_action'])->all();
        $lead->update($data);

        return new LeadResource($lead->fresh());
    }

    public function destroy(Lead $lead): JsonResponse
    {
        $this->authorize('delete', $lead);
        $lead->delete();

        return response()->json(null, 204);
    }
}
