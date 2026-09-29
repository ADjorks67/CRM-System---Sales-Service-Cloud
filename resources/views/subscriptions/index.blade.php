@extends('layouts.app')

@section('title', 'My Subscriptions — '.config('app.name'))

@section('content')
    <div class="mb-4">
        <h1>My Report Subscriptions</h1>
        <p class="text-sm text-text/70">Scheduled CSV email delivery (FR-RPT-006).</p>
    </div>

    <div class="overflow-x-auto rounded bg-card shadow-[var(--shadow-card)]">
        <table class="min-w-full text-left text-sm">
            <thead class="border-b border-black/10 bg-black/[0.03]">
                <tr>
                    <th class="px-3 py-2 font-semibold">Report</th>
                    <th class="px-3 py-2 font-semibold">Frequency</th>
                    <th class="px-3 py-2 font-semibold">Next run</th>
                    <th class="px-3 py-2 font-semibold">Last sent</th>
                    <th class="px-3 py-2 font-semibold">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($subscriptions as $subscription)
                    <tr class="border-b border-black/5">
                        <td class="px-3 py-2">{{ $subscription->savedReport?->name ?? '—' }}</td>
                        <td class="px-3 py-2">{{ ucfirst($subscription->frequency) }}</td>
                        <td class="px-3 py-2">{{ $subscription->next_run_at?->toDateTimeString() ?? '—' }}</td>
                        <td class="px-3 py-2">{{ $subscription->last_sent_at?->toDateTimeString() ?? '—' }}</td>
                        <td class="px-3 py-2">
                            <a href="{{ route('subscriptions.edit', $subscription) }}" class="text-secondary no-underline">Edit</a>
                            <form method="post" action="{{ route('subscriptions.destroy', $subscription) }}" class="inline" onsubmit="return confirm('Delete this subscription?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="ml-2 text-error">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td class="px-3 py-4 text-text/70" colspan="5">No subscriptions yet. Open a saved report and click Subscribe.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
