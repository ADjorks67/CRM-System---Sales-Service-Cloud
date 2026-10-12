@extends('layouts.app')

@section('title', 'New Opportunity — '.config('app.name'))

@section('content')
    <div class="mb-4">
        <x-ui.back :href="route('opportunities.index')" label="Back to Opportunities" />
        <h1 class="mt-2">New Opportunity</h1>
    </div>

    <form method="post" action="{{ route('opportunities.store') }}" class="max-w-4xl space-y-4 crm-card p-6">
        @csrf
        @include('opportunities._form')

        <div class="flex flex-wrap gap-2 pt-2">
            <button type="submit" name="save_action" value="save" class="crm-btn-primary">Save</button>
            <button type="submit" name="save_action" value="save_new" class="crm-btn-secondary">Save &amp; New</button>
            <a href="{{ route('opportunities.index') }}" class="crm-btn-secondary">Cancel</a>
        </div>
    </form>
@endsection
