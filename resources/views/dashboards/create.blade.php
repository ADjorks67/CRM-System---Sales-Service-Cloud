@extends('layouts.app')

@section('title', 'New Dashboard — '.config('app.name'))

@section('content')
    <div class="mb-4">
        <p class="text-sm"><a href="{{ route('dashboards.index') }}" class="text-secondary no-underline">Dashboards</a></p>
        <h1>New Dashboard</h1>
    </div>

    <form method="post" action="{{ route('dashboards.store') }}" class="grid max-w-3xl gap-4">
        @csrf
        @include('dashboards._form', ['dashboard' => null])
        <button type="submit" class="inline-flex min-h-11 w-fit items-center rounded bg-secondary px-4 py-2 text-sm font-semibold text-white">Create</button>
    </form>
@endsection
