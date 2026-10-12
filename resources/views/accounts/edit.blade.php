@extends('layouts.app')

@section('title', 'Edit '.$account->name.' — '.config('app.name'))

@section('content')
    <div class="mb-4">
        <x-ui.back :href="route('accounts.show', $account)" label="Back to {{ $account->name }}" />
        <h1 class="mt-2">Edit Account</h1>
    </div>

    <form method="post" action="{{ route('accounts.update', $account) }}" class="max-w-4xl space-y-4 crm-card p-6" data-account-form>
        @csrf
        @method('PUT')
        @include('accounts._form')

        <div class="flex flex-wrap gap-2 pt-2">
            <button type="submit" name="save_action" value="save" class="crm-btn-primary">Save</button>
            <button type="submit" name="save_action" value="save_new" class="crm-btn-secondary">Save &amp; New</button>
            <a href="{{ route('accounts.show', $account) }}" class="crm-btn-secondary">Cancel</a>
        </div>
    </form>
@endsection

@push('scripts')
    @vite('resources/js/accounts.js')
@endpush
