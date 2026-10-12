@extends('layouts.app')

@section('title', 'New Account — '.config('app.name'))

@section('content')
    <div class="mb-4">
        <x-ui.back :href="route('accounts.index')" label="Back to Accounts" />
        <h1 class="mt-2">New Account</h1>
    </div>

    <form method="post" action="{{ route('accounts.store') }}" class="max-w-4xl space-y-4 crm-card p-6" data-account-form>
        @csrf
        @include('accounts._form')

        <div class="flex flex-wrap gap-2 pt-2">
            <button type="submit" name="save_action" value="save" class="crm-btn-primary">Save</button>
            <button type="submit" name="save_action" value="save_new" class="crm-btn-secondary">Save &amp; New</button>
            <a href="{{ route('accounts.index') }}" class="crm-btn-secondary">Cancel</a>
        </div>
    </form>
@endsection

@push('scripts')
    @vite('resources/js/accounts.js')
@endpush
