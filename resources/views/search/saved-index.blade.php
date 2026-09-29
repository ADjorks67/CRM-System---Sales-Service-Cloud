@extends('layouts.app')

@section('title', 'Saved Searches — '.config('app.name'))

@section('content')
    <div class="mb-4 flex flex-wrap items-end justify-between gap-3">
        <div>
            <h1>Saved Searches</h1>
            <p class="text-sm text-text/70">Your advanced searches (FR-SRCH-003).</p>
        </div>
        <a href="{{ route('search.advanced.create') }}" class="inline-flex min-h-11 items-center rounded bg-secondary px-4 py-2 text-sm font-semibold text-white no-underline">New advanced search</a>
    </div>

    <div class="overflow-x-auto rounded bg-card shadow-[var(--shadow-card)]">
        <table class="min-w-full text-left text-sm">
            <thead class="border-b border-black/10 bg-black/[0.03]">
                <tr>
                    <th class="px-3 py-2 font-semibold">Name</th>
                    <th class="px-3 py-2 font-semibold">Object</th>
                    <th class="px-3 py-2 font-semibold">Updated</th>
                    <th class="px-3 py-2 font-semibold">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($savedSearches as $saved)
                    <tr class="border-b border-black/5">
                        <td class="px-3 py-2"><a href="{{ route('saved-searches.show', $saved) }}" class="no-underline">{{ $saved->name }}</a></td>
                        <td class="px-3 py-2">{{ $objectLabels[$saved->object_type] ?? $saved->object_type }}</td>
                        <td class="px-3 py-2">{{ $saved->updated_at?->toDateTimeString() }}</td>
                        <td class="px-3 py-2">
                            <a href="{{ route('saved-searches.show', $saved) }}" class="text-secondary no-underline">Rerun</a>
                            <form method="post" action="{{ route('saved-searches.destroy', $saved) }}" class="inline" onsubmit="return confirm('Delete this saved search?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="ml-2 text-error">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td class="px-3 py-4 text-text/70" colspan="4">No saved searches yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $savedSearches->links() }}</div>
@endsection
