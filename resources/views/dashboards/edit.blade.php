@extends('layouts.app')

@section('title', 'Edit '.$dashboard->name.' — '.config('app.name'))

@section('content')
    <x-breadcrumbs :items="[
        ['label' => 'Dashboards', 'url' => route('dashboards.index')],
        ['label' => $dashboard->name, 'url' => route('dashboards.show', $dashboard)],
        ['label' => 'Edit'],
    ]" />

    <div class="mb-4">
        <h1>Edit Dashboard</h1>
    </div>

    <form method="post" action="{{ route('dashboards.update', $dashboard) }}" class="grid max-w-3xl gap-4">
        @csrf
        @method('PUT')
        @include('dashboards._form')
        <button type="submit" class="inline-flex min-h-11 w-fit items-center rounded bg-secondary px-4 py-2 text-sm font-semibold text-white">Save</button>
    </form>
@endsection
