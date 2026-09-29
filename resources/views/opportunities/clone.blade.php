@extends('layouts.app')

@section('title', 'Clone '.$opportunity->displayName().' — '.config('app.name'))

@section('content')
    <h1 class="mb-2">Clone Opportunity</h1>
    <p class="mb-6 text-sm text-text/70">Create a copy of <strong>{{ $opportunity->displayName() }}</strong> starting at Qualification stage.</p>

    <form method="post" action="{{ route('opportunities.clone.store', $opportunity) }}" class="max-w-lg space-y-4 rounded bg-card p-6 shadow-[var(--shadow-card)]">
        @csrf
        <label class="flex min-h-11 items-start gap-2 text-sm">
            <input type="checkbox" name="include_related" value="1" class="mt-1 size-4 rounded border-black/20" @checked(old('include_related'))>
            <span>
                Include related records
                <span class="block text-text/60">Products and quotes will copy when those modules are available.</span>
            </span>
        </label>

        <div class="flex flex-wrap gap-2 pt-2">
            <button type="submit" class="inline-flex min-h-11 items-center rounded bg-secondary px-4 py-2 text-sm font-semibold text-white">Clone</button>
            <a href="{{ route('opportunities.show', $opportunity) }}" class="inline-flex min-h-11 items-center rounded border border-black/20 px-4 py-2 text-sm text-text no-underline">Cancel</a>
        </div>
    </form>
@endsection
