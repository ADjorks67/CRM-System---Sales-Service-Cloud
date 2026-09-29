@extends('layouts.app')

@section('title', 'Edit Event — '.config('app.name'))

@section('content')
    <div class="mb-4">
        <h1>Edit Event</h1>
        <p class="text-sm text-text/70"><a href="{{ route('events.show', $event) }}">Back to Event</a></p>
    </div>

    <form method="post" action="{{ route('events.update', $event) }}" class="space-y-4 rounded bg-card p-4 shadow-[var(--shadow-card)]">
        @csrf
        @method('PUT')
        @include('events._form')
        <div class="flex flex-wrap gap-2">
            <button type="submit" class="inline-flex min-h-11 items-center rounded bg-secondary px-4 py-2 text-sm font-semibold text-white">Save</button>
            <a href="{{ route('events.show', $event) }}" class="inline-flex min-h-11 items-center rounded px-4 py-2 text-sm no-underline">Cancel</a>
        </div>
    </form>
@endsection
