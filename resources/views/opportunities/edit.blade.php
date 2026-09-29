@extends('layouts.app')

@section('title', 'Edit '.$opportunity->displayName().' — '.config('app.name'))

@section('content')
    <h1 class="mb-4">Edit Opportunity</h1>

    <form method="post" action="{{ route('opportunities.update', $opportunity) }}" class="max-w-4xl space-y-4 rounded bg-card p-6 shadow-[var(--shadow-card)]">
        @csrf
        @method('PUT')
        @include('opportunities._form', ['opportunity' => $opportunity])

        <div class="flex flex-wrap gap-2 pt-2">
            <button type="submit" name="save_action" value="save" class="inline-flex min-h-11 items-center rounded bg-secondary px-4 py-2 text-sm font-semibold text-white">Save</button>
            <button type="submit" name="save_action" value="save_new" class="inline-flex min-h-11 items-center rounded border border-secondary px-4 py-2 text-sm font-semibold text-secondary">Save &amp; New</button>
            <a href="{{ route('opportunities.show', $opportunity) }}" class="inline-flex min-h-11 items-center rounded border border-black/20 px-4 py-2 text-sm text-text no-underline">Cancel</a>
        </div>
    </form>
@endsection
