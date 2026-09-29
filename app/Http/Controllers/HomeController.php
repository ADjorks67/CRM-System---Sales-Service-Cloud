<?php

namespace App\Http\Controllers;

use App\Services\HomeDashboardService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __construct(private readonly HomeDashboardService $dashboard) {}

    public function __invoke(Request $request): View
    {
        $period = $request->string('period')->toString();
        if (! in_array($period, ['current_year', 'last_year'], true)) {
            $period = 'current_year';
        }

        $data = $this->dashboard->build($request->user(), $period);

        return view('home', $data);
    }
}
