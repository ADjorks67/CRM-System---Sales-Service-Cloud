<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSavedReportRequest;
use App\Http\Requests\UpdateSavedReportRequest;
use App\Models\SavedReport;
use App\Services\ReportBuilderService;
use App\Services\ReportExportService;
use App\Support\ReportFieldCatalog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SavedReportController extends Controller
{
    public function __construct(
        private readonly ReportBuilderService $reportBuilder,
        private readonly ReportExportService $reportExport,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', SavedReport::class);

        $reports = SavedReport::query()
            ->visibleTo($request->user())
            ->with('owner')
            ->orderBy('folder')
            ->orderBy('name')
            ->get()
            ->groupBy('folder');

        return view('saved-reports.index', [
            'groups' => $reports,
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorize('create', SavedReport::class);

        $type = $request->string('type')->toString();
        if (! in_array($type, ReportFieldCatalog::REPORT_TYPES, true)) {
            $type = 'lead';
        }

        return view('saved-reports.create', $this->builderLookups($type));
    }

    public function store(StoreSavedReportRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $definition = ReportFieldCatalog::sanitizeDefinition(
            $validated['report_type'],
            $validated['definition'] ?? [],
        );

        $report = SavedReport::query()->create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'folder' => $validated['folder'] ?? 'private',
            'report_type' => $validated['report_type'],
            'definition' => $definition,
            'is_private' => $validated['is_private'] ?? true,
            'owner_id' => $request->user()->id,
        ]);

        return redirect()
            ->route('saved-reports.show', $report)
            ->with('success', 'Report saved.');
    }

    public function show(Request $request, SavedReport $savedReport): View
    {
        $this->authorize('view', $savedReport);

        $result = $this->reportBuilder->runSaved($request->user(), $savedReport);

        return view('saved-reports.show', [
            'savedReport' => $savedReport,
            'result' => $result,
        ]);
    }

    public function edit(SavedReport $savedReport): View
    {
        $this->authorize('update', $savedReport);

        return view('saved-reports.edit', array_merge(
            $this->builderLookups($savedReport->report_type),
            ['savedReport' => $savedReport],
        ));
    }

    public function update(UpdateSavedReportRequest $request, SavedReport $savedReport): RedirectResponse
    {
        $validated = $request->validated();
        $definition = ReportFieldCatalog::sanitizeDefinition(
            $savedReport->report_type,
            $validated['definition'] ?? $savedReport->definition ?? [],
        );

        $savedReport->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'folder' => $validated['folder'] ?? 'private',
            'definition' => $definition,
            'is_private' => $validated['is_private'] ?? true,
        ]);

        return redirect()
            ->route('saved-reports.show', $savedReport)
            ->with('success', 'Report updated.');
    }

    public function destroy(SavedReport $savedReport): RedirectResponse
    {
        $this->authorize('delete', $savedReport);

        $savedReport->delete();

        return redirect()
            ->route('saved-reports.index')
            ->with('success', 'Report deleted.');
    }

    public function export(Request $request, SavedReport $savedReport)
    {
        $this->authorize('export', $savedReport);

        $result = $this->reportBuilder->runSaved($request->user(), $savedReport);
        $filename = str($savedReport->name)->slug()->append('.csv')->toString();

        return $this->reportExport->csv(
            $filename,
            $result['columns'],
            $result['column_labels'],
            $result['rows'],
        );
    }

    public function print(Request $request, SavedReport $savedReport): View
    {
        $this->authorize('view', $savedReport);

        $result = $this->reportBuilder->runSaved($request->user(), $savedReport);

        return view('saved-reports.print', [
            'savedReport' => $savedReport,
            'result' => $result,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function builderLookups(string $type): array
    {
        $fields = ReportFieldCatalog::fields($type);

        return [
            'reportType' => $type,
            'reportTypes' => ReportFieldCatalog::typeOptions(),
            'fields' => $fields,
            'filterOperators' => [
                'eq' => 'equals',
                'neq' => 'not equal',
                'contains' => 'contains',
                'gte' => 'on or after',
                'lte' => 'on or before',
            ],
        ];
    }
}
