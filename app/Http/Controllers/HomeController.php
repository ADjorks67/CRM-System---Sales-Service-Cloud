<?php

namespace App\Http\Controllers;

use App\Services\AssistantRecommendationService;
use App\Services\HomeDashboardService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __construct(
        private readonly HomeDashboardService $dashboard,
        private readonly AssistantRecommendationService $assistant,
    ) {}

    public function __invoke(Request $request): View
    {
        $period = $request->string('period')->toString();
        if (! in_array($period, ['current_year', 'last_year'], true)) {
            $period = 'current_year';
        }

        $data = $this->dashboard->build($request->user(), $period);
        $data['recommendations'] = $this->assistant->recommendationsFor($request->user());

        return view('home', $data);
    }

    public function dismissAssistant(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'recommendation_key' => ['required', 'string', 'max:120'],
        ]);

        $this->assistant->dismiss($request->user(), $validated['recommendation_key']);

        return redirect()
            ->route('home')
            ->with('success', 'Recommendation dismissed.');
    }
}
