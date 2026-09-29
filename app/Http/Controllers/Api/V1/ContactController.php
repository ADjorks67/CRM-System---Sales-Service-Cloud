<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreContactRequest;
use App\Http\Requests\UpdateContactRequest;
use App\Http\Resources\Api\V1\ContactResource;
use App\Models\Contact;
use App\Support\ApiFieldCatalog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ContactController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Contact::class);

        $query = Contact::query()->visibleTo($request->user());
        ApiFieldCatalog::applyFilters($query, $request, 'contacts');
        ApiFieldCatalog::applySort($query, $request, 'contacts', 'last_name');

        return ContactResource::collection($query->paginate(ApiFieldCatalog::perPage($request)));
    }

    public function store(StoreContactRequest $request): JsonResponse
    {
        $data = collect($request->validated())->except(['save_action'])->all();
        $data['owner_id'] = $data['owner_id'] ?? $request->user()->id;

        $contact = Contact::query()->create($data);

        return (new ContactResource($contact))->response()->setStatusCode(201);
    }

    public function show(Contact $contact): ContactResource
    {
        $this->authorize('view', $contact);

        return new ContactResource($contact);
    }

    public function update(UpdateContactRequest $request, Contact $contact): ContactResource
    {
        $data = collect($request->validated())->except(['save_action'])->all();
        $contact->update($data);

        return new ContactResource($contact->fresh());
    }

    public function destroy(Contact $contact): JsonResponse
    {
        $this->authorize('delete', $contact);
        $contact->delete();

        return response()->json(null, 204);
    }
}
