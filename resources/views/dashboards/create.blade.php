@extends('layouts.app')

@section('title', 'New Dashboard — '.config('app.name'))

@section('content')
    <x-breadcrumbs :items="[
        ['label' => 'Dashboards', 'url' => route('dashboards.index')],
        ['label' => 'New Dashboard'],
    ]" />

    <div class="mb-4">
        <h1>New Dashboard</h1>
    </div>

    <form method="post" action="{{ route('dashboards.store') }}" class="grid max-w-3xl gap-4">
        @csrf
        @include('dashboards._form', ['dashboard' => null])
        <button type="submit" class="crm-btn-primary">Create</button>
    </form>
@endsection
