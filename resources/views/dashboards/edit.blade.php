@extends('layouts.app')

@section('title', 'Edit '.$dashboard->name.' — '.config('app.name'))

@section('content')
    <div class="mb-4">
        <p class="text-sm"><a href="{{ route('dashboards.show', $dashboard) }}" class="text-secondary no-underline">Back to dashboard</a></p>
        <h1>Edit Dashboard</h1>
    </div>

    <form method="post" action="{{ route('dashboards.update', $dashboard) }}" class="grid max-w-3xl gap-4">
        @csrf
        @method('PUT')
        @include('dashboards._form')
        <button type="submit" class="inline-flex min-h-11 w-fit items-center rounded bg-secondary px-4 py-2 text-sm font-semibold text-white">Save</button>
    </form>
@endsection
