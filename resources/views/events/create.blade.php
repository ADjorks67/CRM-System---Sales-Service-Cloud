@extends('layouts.app')

@section('title', 'New Event — '.config('app.name'))

@section('content')
    <div class="mb-4">
        <h1>New Event</h1>
        <p class="text-sm text-text/70"><a href="{{ route('calendar.index') }}">Back to Calendar</a></p>
    </div>

    <form method="post" action="{{ route('events.store') }}" class="space-y-4 rounded bg-card p-4 shadow-[var(--shadow-card)]">
        @csrf
        @include('events._form')
        <div class="flex flex-wrap gap-2">
            <button type="submit" name="save_action" value="save" class="inline-flex min-h-11 items-center rounded bg-secondary px-4 py-2 text-sm font-semibold text-white">Save</button>
            <button type="submit" name="save_action" value="save_new" class="inline-flex min-h-11 items-center rounded border border-black/20 px-4 py-2 text-sm">Save &amp; New</button>
            <a href="{{ route('calendar.index') }}" class="inline-flex min-h-11 items-center rounded px-4 py-2 text-sm no-underline">Cancel</a>
        </div>
    </form>
@endsection
