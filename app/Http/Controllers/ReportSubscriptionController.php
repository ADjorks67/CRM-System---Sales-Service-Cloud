<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreReportSubscriptionRequest;
use App\Http\Requests\UpdateReportSubscriptionRequest;
use App\Models\ReportSubscription;
use App\Models\SavedReport;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReportSubscriptionController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', ReportSubscription::class);

        $subscriptions = ReportSubscription::query()
            ->where('user_id', $request->user()->id)
            ->with('savedReport')
            ->orderBy('next_run_at')
            ->get();

        return view('subscriptions.index', [
            'subscriptions' => $subscriptions,
        ]);
    }

    public function create(SavedReport $savedReport): View
    {
        $this->authorize('create', [ReportSubscription::class, $savedReport]);

        return view('subscriptions.create', [
            'savedReport' => $savedReport,
            'subscription' => null,
        ]);
    }

    public function store(StoreReportSubscriptionRequest $request, SavedReport $savedReport): RedirectResponse
    {
        $validated = $request->validated();

        $subscription = new ReportSubscription([
            'saved_report_id' => $savedReport->id,
            'user_id' => $request->user()->id,
            'frequency' => $validated['frequency'],
            'day_of_week' => $validated['frequency'] === 'weekly' ? ($validated['day_of_week'] ?? 1) : null,
            'day_of_month' => $validated['frequency'] === 'monthly' ? ($validated['day_of_month'] ?? 1) : null,
            'time_of_day' => $validated['time_of_day'].':00',
        ]);
        $subscription->next_run_at = $subscription->computeNextRunAt();
        $subscription->save();

        return redirect()
            ->route('subscriptions.index')
            ->with('success', 'Subscription created.');
    }

    public function edit(ReportSubscription $subscription): View
    {
        $this->authorize('update', $subscription);
        $subscription->load('savedReport');

        return view('subscriptions.edit', [
            'subscription' => $subscription,
            'savedReport' => $subscription->savedReport,
        ]);
    }

    public function update(UpdateReportSubscriptionRequest $request, ReportSubscription $subscription): RedirectResponse
    {
        $validated = $request->validated();

        $subscription->fill([
            'frequency' => $validated['frequency'],
            'day_of_week' => $validated['frequency'] === 'weekly' ? ($validated['day_of_week'] ?? 1) : null,
            'day_of_month' => $validated['frequency'] === 'monthly' ? ($validated['day_of_month'] ?? 1) : null,
            'time_of_day' => $validated['time_of_day'].':00',
        ]);
        $subscription->next_run_at = $subscription->computeNextRunAt();
        $subscription->save();

        return redirect()
            ->route('subscriptions.index')
            ->with('success', 'Subscription updated.');
    }

    public function destroy(ReportSubscription $subscription): RedirectResponse
    {
        $this->authorize('delete', $subscription);
        $subscription->delete();

        return redirect()
            ->route('subscriptions.index')
            ->with('success', 'Subscription deleted.');
    }
}
