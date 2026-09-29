@extends('layouts.app')

@section('title', 'Home — '.config('app.name'))

@section('content')
    <div class="rounded bg-card p-6 shadow-[var(--shadow-card)]">
        <h1 class="mb-2">Home</h1>
        <p class="mb-4 text-sm text-text/80">
            Signed in as <strong>{{ auth()->user()->name }}</strong>
            @if (auth()->user()->role)
                ({{ auth()->user()->role->name }})
            @endif.
        </p>
        <p class="text-sm text-text/80">
            Phase 1 foundation is ready. Open <a href="{{ route('accounts.index') }}">Accounts</a> for the empty module shell, or manage users if you are a System Administrator.
        </p>
    </div>
@endsection
