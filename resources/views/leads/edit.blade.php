@extends('layouts.app')

@section('title', 'Edit '.$lead->displayName().' — '.config('app.name'))

@section('content')
    <div class="mb-4">
        <x-ui.back :href="route('leads.show', $lead)" label="Back to {{ $lead->displayName() }}" />
        <h1 class="mt-2">Edit Lead</h1>
    </div>

    <form method="post" action="{{ route('leads.update', $lead) }}" class="max-w-4xl space-y-4 crm-card p-6">
        @csrf
        @method('PUT')
        @include('leads._form')

        <div class="flex flex-wrap gap-2 pt-2">
            <button type="submit" name="save_action" value="save" class="crm-btn-primary">Save</button>
            <button type="submit" name="save_action" value="save_new" class="crm-btn-secondary">Save &amp; New</button>
            <a href="{{ route('leads.show', $lead) }}" class="crm-btn-secondary">Cancel</a>
        </div>
    </form>
@endsection
