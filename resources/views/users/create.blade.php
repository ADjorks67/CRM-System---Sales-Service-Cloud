@extends('layouts.app')

@section('title', 'New User — '.config('app.name'))

@section('content')
    <h1 class="mb-4">New User</h1>

    <form method="post" action="{{ route('users.store') }}" class="max-w-2xl space-y-4 rounded bg-card p-6 shadow-[var(--shadow-card)]">
        @csrf
        @include('users._form')

        <div class="flex flex-wrap gap-2 pt-2">
            <button type="submit" class="inline-flex min-h-11 items-center rounded bg-secondary px-4 py-2 text-sm font-semibold text-white">Save</button>
            <a href="{{ route('users.index') }}" class="inline-flex min-h-11 items-center rounded border border-black/20 px-4 py-2 text-sm text-text no-underline">Cancel</a>
        </div>
    </form>
@endsection
