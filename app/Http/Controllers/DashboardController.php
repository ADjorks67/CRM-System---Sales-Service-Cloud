<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDashboardRequest;
use App\Http\Requests\UpdateDashboardRequest;
use App\Models\Dashboard;
use App\Models\DashboardWidget;
use App\Models\SavedReport;
use App\Models\User;
use App\Reports\ReportCatalog;
use App\Services\DashboardFilterService;
use App\Services\DashboardWidgetDataService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        private readonly DashboardWidgetDataService $widgetData,
        private readonly DashboardFilterService $dashboardFilters,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Dashboard::class);

        $dashboards = Dashboard::query()
            ->visibleTo($request->user())
            ->with('owner')
            ->orderBy('folder')
            ->orderBy('name')
            ->get()
            ->groupBy('folder');

        return view('dashboards.index', [
            'groups' => $dashboards,
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorize('create', Dashboard::class);

        return view('dashboards.create', $this->formLookups($request));
    }

    public function store(StoreDashboardRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $dashboard = DB::transaction(function () use ($validated, $request): Dashboard {
            $dashboard = Dashboard::query()->create([
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'folder' => $validated['folder'] ?? 'private',
                'is_private' => $validated['is_private'] ?? true,
                'owner_id' => $request->user()->id,
            ]);

            $this->syncWidgets($dashboard, $validated['widgets'] ?? []);

            return $dashboard;
        });

        return redirect()
            ->route('dashboards.show', $dashboard)
            ->with('success', 'Dashboard created.');
    }

    public function show(Request $request, Dashboard $dashboard): View
    {
        $this->authorize('view', $dashboard);

        $dashboard->load('widgets.savedReport');
        $filters = $this->dashboardFilters->get($request);

        $widgetPayloads = $dashboard->widgets->map(function (DashboardWidget $widget) use ($request): array {
            try {
                return $this->widgetData->resolve($request->user(), $widget, $request);
            } catch (\Throwable) {
                return [
                    'title' => $widget->title,
                    'widget_type' => $widget->widget_type,
                    'error' => 'Unable to load widget.',
                    'columns' => [],
                    'column_labels' => [],
                    'rows' => collect(),
                    'chart' => null,
                    'metric' => null,
                ];
            }
        });

        return view('dashboards.show', [
            'dashboard' => $dashboard,
            'widgetPayloads' => $widgetPayloads,
            'filters' => $filters,
            'owners' => User::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function edit(Request $request, Dashboard $dashboard): View
    {
        $this->authorize('update', $dashboard);

        $dashboard->load('widgets');

        return view('dashboards.edit', array_merge(
            $this->formLookups($request),
            ['dashboard' => $dashboard],
        ));
    }

    public function update(UpdateDashboardRequest $request, Dashboard $dashboard): RedirectResponse
    {
        $validated = $request->validated();

        DB::transaction(function () use ($dashboard, $validated): void {
            $dashboard->update([
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'folder' => $validated['folder'] ?? 'private',
                'is_private' => $validated['is_private'] ?? true,
            ]);

            $dashboard->widgets()->delete();
            $this->syncWidgets($dashboard, $validated['widgets'] ?? []);
        });

        return redirect()
            ->route('dashboards.show', $dashboard)
            ->with('success', 'Dashboard updated.');
    }

    public function destroy(Dashboard $dashboard): RedirectResponse
    {
        $this->authorize('delete', $dashboard);

        $dashboard->delete();

        return redirect()
            ->route('dashboards.index')
            ->with('success', 'Dashboard deleted.');
    }

    public function clone(Request $request, Dashboard $dashboard): RedirectResponse
    {
        $this->authorize('clone', $dashboard);

        $dashboard->load('widgets');

        $copy = DB::transaction(function () use ($dashboard, $request): Dashboard {
            $new = Dashboard::query()->create([
                'name' => $dashboard->name.' (Copy)',
                'description' => $dashboard->description,
                'folder' => $dashboard->folder,
                'is_private' => $dashboard->is_private,
                'layout' => $dashboard->layout,
                'owner_id' => $request->user()->id,
            ]);

            foreach ($dashboard->widgets as $widget) {
                $new->widgets()->create($widget->only([
                    'title', 'widget_type', 'source_type', 'source_key', 'saved_report_id',
                    'grid_x', 'grid_y', 'grid_w', 'grid_h', 'config',
                ]));
            }

            return $new;
        });

        return redirect()
            ->route('dashboards.edit', $copy)
            ->with('success', 'Dashboard cloned.');
    }

    public function storeFilters(Request $request): RedirectResponse
    {
        $this->authorize('viewAny', Dashboard::class);

        $validated = $request->validate([
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'owner_id' => ['nullable', 'integer', 'exists:users,id'],
            'redirect' => ['nullable', 'string'],
        ]);

        $this->dashboardFilters->store($validated);

        $redirect = $validated['redirect'] ?? route('dashboards.index');

        return redirect($redirect)->with('success', 'Dashboard filters updated.');
    }

    /**
     * @param  list<array<string, mixed>>  $widgets
     */
    private function syncWidgets(Dashboard $dashboard, array $widgets): void
    {
        foreach (array_slice($widgets, 0, 20) as $index => $widget) {
            $dashboard->widgets()->create([
                'title' => $widget['title'],
                'widget_type' => $widget['widget_type'],
                'source_type' => $widget['source_type'],
                'source_key' => $widget['source_key'] ?? null,
                'saved_report_id' => $widget['saved_report_id'] ?? null,
                'grid_x' => (int) ($widget['grid_x'] ?? 0),
                'grid_y' => (int) ($widget['grid_y'] ?? $index),
                'grid_w' => (int) ($widget['grid_w'] ?? 6),
                'grid_h' => (int) ($widget['grid_h'] ?? 4),
            ]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function formLookups(Request $request): array
    {
        return [
            'prebuiltReports' => collect(ReportCatalog::all())->map(fn ($report) => [
                'key' => $report->key(),
                'title' => $report->title(),
            ]),
            'savedReports' => SavedReport::query()
                ->visibleTo($request->user())
                ->orderBy('name')
                ->get(['id', 'name']),
            'widgetTypes' => [
                'chart' => 'Chart',
                'table' => 'Table',
                'metric' => 'Metric',
                'gauge' => 'Gauge',
            ],
        ];
    }
}
