@extends('layouts.app')

@section('title', 'New Event — '.config('app.name'))

@section('content')
    <div class="mb-4">
        <x-ui.back :href="route('calendar.index')" label="Back to Calendar" />
        <h1 class="mt-2">New Event</h1>
    </div>

    <form method="post" action="{{ route('events.store') }}" class="space-y-4 crm-card p-4 sm:p-5">
        @csrf
        @include('events._form')
        <div class="flex flex-wrap gap-2">
            <button type="submit" name="save_action" value="save" class="crm-btn-primary">Save</button>
            <button type="submit" name="save_action" value="save_new" class="crm-btn-secondary">Save &amp; New</button>
            <a href="{{ route('calendar.index') }}" class="crm-btn-secondary">Cancel</a>
        </div>
    </form>
@endsection
