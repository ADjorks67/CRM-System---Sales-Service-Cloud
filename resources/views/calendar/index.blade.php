@extends('layouts.app')

@section('title', 'Calendar — '.config('app.name'))

@section('content')
    <div class="mb-4 flex flex-wrap items-end justify-between gap-3">
        <div>
            <h1>Calendar</h1>
            <p class="text-sm text-text/70">Day, week, month, and table views. Drag events to reschedule (FR-CAL-001..004).</p>
        </div>
        <a href="{{ route('events.create') }}" class="inline-flex min-h-11 items-center rounded bg-secondary px-4 py-2 text-sm font-semibold text-white no-underline">New Event</a>
    </div>

    <form method="get" action="{{ route('calendar.index') }}" class="mb-4 flex flex-wrap items-center gap-4 rounded bg-card p-3 shadow-[var(--shadow-card)]">
        <fieldset class="flex flex-wrap gap-4 border-0 p-0">
            <legend class="sr-only">My Calendars</legend>
            <label class="flex min-h-11 items-center gap-2 text-sm">
                <input type="hidden" name="my_events" value="0">
                <input type="checkbox" name="my_events" value="1" class="size-4 rounded border-black/20" @checked($showMine) onchange="this.form.submit()">
                <span class="inline-flex items-center gap-1">
                    <span class="inline-block size-3 rounded-sm" style="background:#0176d3" aria-hidden="true"></span>
                    My Events
                </span>
            </label>
            <label class="flex min-h-11 items-center gap-2 text-sm">
                <input type="hidden" name="public_team" value="0">
                <input type="checkbox" name="public_team" value="1" class="size-4 rounded border-black/20" @checked($showPublic) onchange="this.form.submit()">
                <span class="inline-flex items-center gap-1">
                    <span class="inline-block size-3 rounded-sm" style="background:#2e844a" aria-hidden="true"></span>
                    Public / team
                </span>
            </label>
        </fieldset>
        <input type="hidden" name="view" value="{{ $view }}">
    </form>

    <x-calendar
        :events-url="$eventsUrl"
        :view="$view"
        :create-url="$createUrl"
        :reschedule-url-template="$rescheduleUrlTemplate"
        aria-label="CRM calendar"
    />
@endsection
