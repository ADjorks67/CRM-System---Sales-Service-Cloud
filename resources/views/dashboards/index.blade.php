@extends('layouts.app')

@section('title', 'Dashboards — '.config('app.name'))

@section('content')
    <div class="mb-4 flex flex-wrap items-end justify-between gap-3">
        <div>
            <h1>Dashboards</h1>
            <p class="text-sm text-text/70">FR-DASH-001 — folders and dashboard cards.</p>
        </div>
        @can('create', App\Models\Dashboard::class)
            <a href="{{ route('dashboards.create') }}" class="inline-flex min-h-11 items-center rounded bg-secondary px-4 py-2 text-sm font-semibold text-white no-underline">New Dashboard</a>
        @endcan
    </div>

    <div class="grid gap-4">
        @forelse ($groups as $folder => $dashboards)
            <section class="rounded bg-card p-4 shadow-[var(--shadow-card)]">
                <h2 class="mb-3 text-base font-semibold text-primary">{{ ucfirst($folder) }}</h2>
                <ul class="divide-y divide-black/10 text-sm">
                    @foreach ($dashboards as $dashboard)
                        <li class="flex flex-wrap items-center justify-between gap-2 py-2">
                            <div>
                                <a href="{{ route('dashboards.show', $dashboard) }}" class="font-medium">{{ $dashboard->name }}</a>
                                <p class="text-text/60">{{ $dashboard->owner?->name ?? '—' }}</p>
                            </div>
                            <div class="flex flex-wrap gap-3">
                                <a href="{{ route('dashboards.show', $dashboard) }}" class="text-secondary no-underline">View</a>
                                @can('update', $dashboard)
                                    <a href="{{ route('dashboards.edit', $dashboard) }}" class="text-secondary no-underline">Edit</a>
                                @endcan
                                @can('clone', $dashboard)
                                    <form method="post" action="{{ route('dashboards.clone', $dashboard) }}" class="inline">@csrf<button type="submit" class="text-secondary underline">Clone</button></form>
                                @endcan
                                @can('delete', $dashboard)
                                    <form method="post" action="{{ route('dashboards.destroy', $dashboard) }}" class="inline" onsubmit="return confirm('Delete this dashboard?');">@csrf @method('DELETE')<button type="submit" class="text-error underline">Delete</button></form>
                                @endcan
                            </div>
                        </li>
                    @endforeach
                </ul>
            </section>
        @empty
            <p class="text-sm text-text/70">No dashboards yet.</p>
        @endforelse
    </div>
@endsection
