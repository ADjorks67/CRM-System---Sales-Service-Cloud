@extends('layouts.app')

@section('title', 'Clone '.$opportunity->displayName().' — '.config('app.name'))

@section('content')
    <div class="mb-4">
        <x-ui.back :href="route('opportunities.show', $opportunity)" label="Back to {{ $opportunity->displayName() }}" />
        <h1 class="mt-2">Clone Opportunity</h1>
        <p class="text-sm text-muted">Create a copy of <strong>{{ $opportunity->displayName() }}</strong> starting at Qualification stage.</p>
    </div>

    <form method="post" action="{{ route('opportunities.clone.store', $opportunity) }}" class="max-w-lg space-y-4 crm-card p-6">
        @csrf
        <label class="flex min-h-11 items-start gap-2 text-sm">
            <input type="checkbox" name="include_related" value="1" class="mt-1 size-4 rounded border-border" @checked(old('include_related'))>
            <span>
                Include related records
                <span class="block text-muted">Products and quotes will copy when those modules are available.</span>
            </span>
        </label>

        <div class="flex flex-wrap gap-2 pt-2">
            <button type="submit" class="crm-btn-primary">Clone</button>
            <a href="{{ route('opportunities.show', $opportunity) }}" class="crm-btn-secondary">Cancel</a>
        </div>
    </form>
@endsection
