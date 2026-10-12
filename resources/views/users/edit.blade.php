@extends('layouts.app')

@section('title', 'Edit User — '.config('app.name'))

@section('content')
    <div class="mb-4">
        <x-ui.back :href="route('users.index')" label="Back to Users" />
        <h1 class="mt-2">Edit User</h1>
    </div>

    <form method="post" action="{{ route('users.update', $user) }}" class="max-w-2xl space-y-4 crm-card p-6">
        @csrf
        @method('PUT')
        @include('users._form', ['user' => $user])

        <label class="flex min-h-11 items-center gap-2 text-sm">
            <input type="checkbox" name="clear_lock" value="1" class="size-4 rounded border-border">
            Clear lockout
        </label>

        <div class="flex flex-wrap gap-2 pt-2">
            <button type="submit" class="crm-btn-primary">Save</button>
            <a href="{{ route('users.index') }}" class="crm-btn-secondary">Cancel</a>
        </div>
    </form>

    @can('delete', $user)
        <form method="post" action="{{ route('users.destroy', $user) }}" class="mt-6 max-w-2xl" onsubmit="return confirm('Delete this user?')">
            @csrf
            @method('DELETE')
            <button type="submit" class="crm-btn-danger">Delete user</button>
        </form>
    @endcan

    @if ($user->mfa_enabled)
        <form method="post" action="{{ route('users.mfa.disable', $user) }}" class="mt-4 max-w-2xl" onsubmit="return confirm('Disable MFA for this user?')">
            @csrf
            <button type="submit" class="crm-btn-secondary">Disable MFA (admin)</button>
        </form>
    @endif
@endsection
