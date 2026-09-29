<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateSavedSearchRequest;
use App\Models\SavedSearch;
use App\Services\AdvancedSearchService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SavedSearchController extends Controller
{
    public function __construct(private readonly AdvancedSearchService $advancedSearch) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', SavedSearch::class);

        $savedSearches = SavedSearch::query()
            ->where('owner_id', $request->user()->id)
            ->orderByDesc('updated_at')
            ->paginate(25);

        return view('search.saved-index', [
            'savedSearches' => $savedSearches,
            'objectLabels' => $this->advancedSearch->objectLabels(),
        ]);
    }

    public function show(Request $request, SavedSearch $savedSearch): View
    {
        $this->authorize('view', $savedSearch);

        $definition = $savedSearch->definition ?? [];
        $definition['object_type'] = $savedSearch->object_type;
        $results = $this->advancedSearch->search($request->user(), $definition);

        return view('search.advanced', [
            'objectType' => $savedSearch->object_type,
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
            'savedSearch' => $savedSearch,
            'savedSearches' => SavedSearch::query()
                ->where('owner_id', $request->user()->id)
                ->orderByDesc('updated_at')
                ->limit(20)
                ->get(),
        ]);
    }

    public function update(UpdateSavedSearchRequest $request, SavedSearch $savedSearch): RedirectResponse
    {
        $savedSearch->update([
            'name' => $request->validated('name'),
        ]);

        return redirect()
            ->route('saved-searches.show', $savedSearch)
            ->with('success', 'Saved search renamed.');
    }

    public function destroy(SavedSearch $savedSearch): RedirectResponse
    {
        $this->authorize('delete', $savedSearch);
        $savedSearch->delete();

        return redirect()
            ->route('saved-searches.index')
            ->with('success', 'Saved search deleted.');
    }
}
