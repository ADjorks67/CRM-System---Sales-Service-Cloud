<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCrmCaseRequest;
use App\Http\Requests\UpdateCrmCaseRequest;
use App\Http\Resources\Api\V1\CrmCaseResource;
use App\Models\CrmCase;
use App\Support\ApiFieldCatalog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CrmCaseController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', CrmCase::class);

        $query = CrmCase::query()->visibleTo($request->user());
        ApiFieldCatalog::applyFilters($query, $request, 'cases');
        ApiFieldCatalog::applySort($query, $request, 'cases', 'case_number');

        return CrmCaseResource::collection($query->paginate(ApiFieldCatalog::perPage($request)));
    }

    public function store(StoreCrmCaseRequest $request): JsonResponse
    {
        $data = collect($request->validated())->except(['save_action'])->all();
        $data['owner_id'] = $data['owner_id'] ?? $request->user()->id;

        $case = CrmCase::query()->create($data);

        return (new CrmCaseResource($case))->response()->setStatusCode(201);
    }

    public function show(CrmCase $crmCase): CrmCaseResource
    {
        $this->authorize('view', $crmCase);

        return new CrmCaseResource($crmCase);
    }

    public function update(UpdateCrmCaseRequest $request, CrmCase $crmCase): CrmCaseResource
    {
        $data = collect($request->validated())->except(['save_action'])->all();
        $crmCase->update($data);

        return new CrmCaseResource($crmCase->fresh());
    }

    public function destroy(CrmCase $crmCase): JsonResponse
    {
        $this->authorize('delete', $crmCase);
        $crmCase->delete();

        return response()->json(null, 204);
    }
}
