@extends('layouts.app')

@section('title', 'Edit Event — '.config('app.name'))

@section('content')
    <div class="mb-4">
        <x-ui.back :href="route('events.show', $event)" label="Back to Event" />
        <h1 class="mt-2">Edit Event</h1>
    </div>

    <form method="post" action="{{ route('events.update', $event) }}" class="space-y-4 crm-card p-4 sm:p-5">
        @csrf
        @method('PUT')
        @include('events._form')
        <div class="flex flex-wrap gap-2">
            <button type="submit" class="crm-btn-primary">Save</button>
            <a href="{{ route('events.show', $event) }}" class="crm-btn-secondary">Cancel</a>
        </div>
    </form>
@endsection
