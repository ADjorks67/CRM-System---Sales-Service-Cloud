@extends('layouts.app')

@section('title', 'Edit User — '.config('app.name'))

@section('content')
    <h1 class="mb-4">Edit User</h1>

    <form method="post" action="{{ route('users.update', $user) }}" class="max-w-2xl space-y-4 rounded bg-card p-6 shadow-[var(--shadow-card)]">
        @csrf
        @method('PUT')
        @include('users._form', ['user' => $user])

        <label class="flex min-h-11 items-center gap-2 text-sm">
            <input type="checkbox" name="clear_lock" value="1" class="size-4 rounded border-black/20">
            Clear lockout
        </label>

        <div class="flex flex-wrap gap-2 pt-2">
            <button type="submit" class="inline-flex min-h-11 items-center rounded bg-secondary px-4 py-2 text-sm font-semibold text-white">Save</button>
            <a href="{{ route('users.index') }}" class="inline-flex min-h-11 items-center rounded border border-black/20 px-4 py-2 text-sm text-text no-underline">Cancel</a>
        </div>
    </form>

    @can('delete', $user)
        <form method="post" action="{{ route('users.destroy', $user) }}" class="mt-6 max-w-2xl" onsubmit="return confirm('Delete this user?')">
            @csrf
            @method('DELETE')
            <button type="submit" class="inline-flex min-h-11 items-center rounded bg-error px-4 py-2 text-sm font-semibold text-white">Delete user</button>
        </form>
    @endcan

    @if ($user->mfa_enabled)
        <form method="post" action="{{ route('users.mfa.disable', $user) }}" class="mt-4 max-w-2xl" onsubmit="return confirm('Disable MFA for this user?')">
            @csrf
            <button type="submit" class="inline-flex min-h-11 items-center rounded border border-black/20 px-4 py-2 text-sm">Disable MFA (admin)</button>
        </form>
    @endif
@endsection
