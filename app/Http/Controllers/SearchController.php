<?php

namespace App\Http\Controllers;

use App\Services\GlobalSearchService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SearchController extends Controller
{
    public function __construct(private readonly GlobalSearchService $search) {}

    public function index(Request $request): View
    {
        $query = trim($request->string('q')->toString());
        $type = $request->string('type')->toString();
        $type = $type !== '' && isset(GlobalSearchService::OBJECTS[$type]) ? $type : null;

        $payload = ['results' => collect(), 'type' => $type];

        if (mb_strlen($query) >= 2) {
            $payload = $this->search->search($request->user(), $query, $type);
        }

        return view('search.index', [
            'q' => $query,
            'type' => $payload['type'],
            'results' => $payload['results'],
            'recentQueries' => $this->search->recentQueries($request->user()),
            'objectLabels' => [
                'leads' => 'Leads',
                'accounts' => 'Accounts',
                'contacts' => 'Contacts',
                'opportunities' => 'Opportunities',
                'cases' => 'Cases',
            ],
        ]);
    }

    public function suggest(Request $request): JsonResponse
    {
        $query = trim($request->string('q')->toString());

        if (mb_strlen($query) < 2) {
            return response()->json(['groups' => []]);
        }

        $groups = [];

        foreach ($this->search->suggest($request->user(), $query) as $key => $records) {
            $groups[$key] = $records->map(fn ($record) => [
                'id' => $record->getKey(),
                'label' => method_exists($record, 'displayName') ? $record->displayName() : (string) $record->getKey(),
                'url' => $this->recordUrl($key, $record->getKey()),
                'meta' => $this->recordMeta($key, $record),
            ])->values()->all();
        }

        return response()->json([
            'groups' => $groups,
            'recent' => $this->search->recentQueries($request->user())->all(),
        ]);
    }

    private function recordUrl(string $type, int|string $id): string
    {
        return match ($type) {
            'leads' => route('leads.show', $id),
            'accounts' => route('accounts.show', $id),
            'contacts' => route('contacts.show', $id),
            'opportunities' => route('opportunities.show', $id),
            'cases' => route('cases.show', $id),
            default => '#',
        };
    }

    private function recordMeta(string $type, object $record): string
    {
        return match ($type) {
            'leads' => (string) ($record->company ?? ''),
            'accounts' => (string) ($record->phone ?? $record->website ?? ''),
            'contacts' => (string) ($record->email ?? ''),
            'opportunities' => (string) ($record->stage ?? ''),
            'cases' => (string) ($record->case_number ?? ''),
            default => '',
        };
    }
}
