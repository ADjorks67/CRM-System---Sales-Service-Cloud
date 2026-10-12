@extends('layouts.app')

@section('title', $event->subject.' — '.config('app.name'))

@section('content')
    <div class="mb-4 flex flex-wrap items-start justify-between gap-3">
        <div>
            <x-ui.back :href="route('calendar.index')" label="Back to Calendar" class="mb-2" />
            <h1>{{ $event->subject }}</h1>
            <p class="text-sm text-muted">
                {{ $event->starts_at->timezone(config('app.timezone'))->format('M j, Y g:i A') }}
                –
                {{ $event->ends_at->timezone(config('app.timezone'))->format('M j, Y g:i A') }}
                · Owner: {{ $event->owner?->name ?? '—' }}
                @if ($event->is_private)
                    · Private
                @endif
            </p>
        </div>
        <div class="flex flex-wrap gap-2">
            @can('update', $event)
                <a href="{{ route('events.edit', $event) }}" class="crm-btn-secondary">Edit</a>
            @endcan
            @can('delete', $event)
                <form method="post" action="{{ route('events.destroy', $event) }}" onsubmit="return confirm('Delete this event?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="crm-btn-danger">Delete</button>
                </form>
            @endcan
        </div>
    </div>

    <dl class="grid gap-4 crm-card p-4 sm:p-5 md:grid-cols-2">
        <div>
            <dt class="text-xs font-semibold uppercase tracking-wide text-muted">Show As</dt>
            <dd>{{ $showAsOptions[$event->show_as] ?? $event->show_as }}</dd>
        </div>
        <div>
            <dt class="text-xs font-semibold uppercase tracking-wide text-muted">Location</dt>
            <dd>{{ $event->location ?: '—' }}</dd>
        </div>
        <div>
            <dt class="text-xs font-semibold uppercase tracking-wide text-muted">All Day</dt>
            <dd>{{ $event->is_all_day ? 'Yes' : 'No' }}</dd>
        </div>
        <div>
            <dt class="text-xs font-semibold uppercase tracking-wide text-muted">Name (Contact)</dt>
            <dd>
                @if ($event->nameContact)
                    <a href="{{ route('contacts.show', $event->nameContact) }}">{{ trim($event->nameContact->first_name.' '.$event->nameContact->last_name) }}</a>
                @else
                    —
                @endif
            </dd>
        </div>
        <div>
            <dt class="text-xs font-semibold uppercase tracking-wide text-muted">Related To</dt>
            <dd>
                @if ($event->related)
                    {{ ucfirst($event->related_type) }}:
                    @php
                        $relatedUrl = match ($event->related_type) {
                            'account' => route('accounts.show', $event->related),
                            'contact' => route('contacts.show', $event->related),
                            'lead' => route('leads.show', $event->related),
                            'opportunity' => route('opportunities.show', $event->related),
                            default => null,
                        };
                        $relatedLabel = method_exists($event->related, 'displayName')
                            ? $event->related->displayName()
                            : (string) ($event->related->name ?? $event->related_id);
                    @endphp
                    @if ($relatedUrl)
                        <a href="{{ $relatedUrl }}">{{ $relatedLabel }}</a>
                    @else
                        {{ $relatedLabel }}
                    @endif
                @else
                    —
                @endif
            </dd>
        </div>
        <div class="md:col-span-2">
            <dt class="text-xs font-semibold uppercase tracking-wide text-muted">Description</dt>
            <dd class="whitespace-pre-wrap">{{ $event->description ?: '—' }}</dd>
        </div>
    </dl>
@endsection
