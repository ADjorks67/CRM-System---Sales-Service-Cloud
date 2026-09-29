<?php

namespace App\Http\Controllers;

use App\Reports\ReportCatalog;
use Illuminate\Http\Request;
use Illuminate\View\View;
use InvalidArgumentException;

class ReportController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('permission', 'reports.view');

        $reports = collect(ReportCatalog::all())->groupBy(fn ($report) => $report->category());

        return view('reports.index', [
            'groups' => $reports,
        ]);
    }

    public function show(Request $request, string $report): View
    {
        $this->authorize('permission', 'reports.view');

        try {
            $definition = ReportCatalog::find($report);
        } catch (InvalidArgumentException) {
            abort(404);
        }

        $year = (int) $request->integer('year', (int) now()->year);
        $from = now()->setYear($year)->startOfYear();
        $to = now()->setYear($year)->endOfYear();

        $rows = $definition->rows($request->user(), $from, $to);
        $chart = $definition->chart($request->user(), $from, $to);

        $sort = $request->string('sort')->toString();
        $direction = strtolower($request->string('direction')->toString()) === 'desc' ? 'desc' : 'asc';

        if ($sort !== '' && in_array($sort, $definition->columns(), true)) {
            $rows = $direction === 'desc'
                ? $rows->sortByDesc($sort)->values()
                : $rows->sortBy($sort)->values();
        }

        return view('reports.show', [
            'report' => $definition,
            'rows' => $rows,
            'chart' => $chart,
            'year' => $year,
            'sort' => $sort,
            'direction' => $direction,
            'from' => $from,
            'to' => $to,
        ]);
    }
}
