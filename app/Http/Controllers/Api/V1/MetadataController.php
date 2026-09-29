<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Support\ApiFieldCatalog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MetadataController extends Controller
{
    public function show(Request $request, string $object): JsonResponse
    {
        if (! isset(ApiFieldCatalog::OBJECTS[$object])) {
            return response()->json(['message' => 'Unknown object.'], 404);
        }

        $modelClass = ApiFieldCatalog::OBJECTS[$object];
        $this->authorize('viewAny', $modelClass);

        return response()->json([
            'object' => $object,
            'fields' => array_values(ApiFieldCatalog::metadata($object)),
            'max_per_page' => 200,
        ]);
    }
}
