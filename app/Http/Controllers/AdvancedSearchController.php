<?php

namespace App\Http\Controllers;

use App\Http\Requests\RunAdvancedSearchRequest;
use App\Http\Requests\StoreSavedSearchRequest;
use App\Models\SavedSearch;
use App\Services\AdvancedSearchService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdvancedSearchController extends Controller
{
    public function __construct(private readonly AdvancedSearchService $advancedSearch) {}

    public function create(Request $request): View
    {
        $objectType = $request->string('object_type')->toString();
        if (! isset(AdvancedSearchService::OBJECTS[$objectType])) {
            $objectType = 'account';
        }

        return view('search.advanced', [
            'objectType' => $objectType,
            'objectLabels' => $this->advancedSearch->objectLabels(),
            'fieldCatalog' => $this->advancedSearch->fieldCatalog(),
            'operators' => [
                'eq' => 'equals',
                'neq' => 'not equal',
                'contains' => 'contains',
                'starts_with' => 'starts with',
                'gte' => 'greater / on or after',
                'lte' => 'less / on or before',
                'gt' => 'greater than',
                'lt' => 'less than',
            ],
            'results' => null,
            'definition' => [
                'object_type' => $objectType,
                'conditions' => [
                    ['field' => array_key_first($this->advancedSearch->fieldCatalog()[$objectType]), 'operator' => 'contains', 'value' => '', 'logic' => 'AND'],
                ],
                'date_from' => null,
                'date_to' => null,
                'date_field' => 'created_at',
            ],
            'savedSearches' => SavedSearch::query()
                ->where('owner_id', $request->user()->id)
                ->orderByDesc('updated_at')
                ->limit(20)
                ->get(),
        ]);
    }

    public function store(RunAdvancedSearchRequest $request): View
    {
        $definition = $this->advancedSearch->sanitizeDefinition($request->validated());
        $results = $this->advancedSearch->search($request->user(), $definition);

        return view('search.advanced', [
            'objectType' => $definition['object_type'],
            'objectLabels' => $this->advancedSearch->objectLabels(),
            'fieldCatalog' => $this->advancedSearch->fieldCatalog(),
            'operators' => [
                'eq' => 'equals',
                'neq' => 'not equal',
                'contains' => 'contains',
                'starts_with' => 'starts with',
                'gte' => 'greater / on or after',
                'lte' => 'less / on or before',
                'gt' => 'greater than',
                'lt' => 'less than',
            ],
            'results' => $results,
            'definition' => $definition,
            'savedSearches' => SavedSearch::query()
                ->where('owner_id', $request->user()->id)
                ->orderByDesc('updated_at')
                ->limit(20)
                ->get(),
        ]);
    }

    public function save(StoreSavedSearchRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $definition = $this->advancedSearch->sanitizeDefinition($validated);

        $saved = SavedSearch::query()->create([
            'name' => $validated['name'],
            'owner_id' => $request->user()->id,
            'object_type' => $definition['object_type'],
            'definition' => $definition,
        ]);

        return redirect()
            ->route('search.advanced.create', ['object_type' => $saved->object_type])
            ->with('success', 'Search saved.');
    }
}
