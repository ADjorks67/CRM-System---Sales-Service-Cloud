@extends('layouts.app')

@section('title', 'Home — '.config('app.name'))

@section('content')
    <div class="rounded bg-card p-6 shadow-[var(--shadow-card)]">
        <h1 class="mb-2">Home</h1>
        <p class="text-sm text-text/80">
            Phase 0 shell is ready. Primary navigation and design tokens are in place for Phase 1 modules.
        </p>
    </div>
@endsection
