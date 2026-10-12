@extends('layouts.app')

@section('title', 'New Lead — '.config('app.name'))

@section('content')
    <div class="mb-4">
        <x-ui.back :href="route('leads.index')" label="Back to Leads" />
        <h1 class="mt-2">New Lead</h1>
    </div>

    <form method="post" action="{{ route('leads.store') }}" class="max-w-4xl space-y-4 crm-card p-6">
        @csrf
        @include('leads._form')

        <div class="flex flex-wrap gap-2 pt-2">
            <button type="submit" name="save_action" value="save" class="crm-btn-primary">Save</button>
            <button type="submit" name="save_action" value="save_new" class="crm-btn-secondary">Save &amp; New</button>
            <a href="{{ route('leads.index') }}" class="crm-btn-secondary">Cancel</a>
        </div>
    </form>
@endsection
