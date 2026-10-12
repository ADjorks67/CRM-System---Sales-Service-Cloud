@extends('layouts.app')

@section('title', 'New User — '.config('app.name'))

@section('content')
    <div class="mb-4">
        <x-ui.back :href="route('users.index')" label="Back to Users" />
        <h1 class="mt-2">New User</h1>
    </div>

    <form method="post" action="{{ route('users.store') }}" class="max-w-2xl space-y-4 crm-card p-6">
        @csrf
        @include('users._form')

        <div class="flex flex-wrap gap-2 pt-2">
            <button type="submit" class="crm-btn-primary">Save</button>
            <a href="{{ route('users.index') }}" class="crm-btn-secondary">Cancel</a>
        </div>
    </form>
@endsection
