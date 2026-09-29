@extends('layouts.app')

@section('title', 'Edit '.$savedReport->name.' — '.config('app.name'))

@section('content')
    <x-breadcrumbs :items="[
        ['label' => 'Custom Reports', 'url' => route('saved-reports.index')],
        ['label' => $savedReport->name, 'url' => route('saved-reports.show', $savedReport)],
        ['label' => 'Edit'],
    ]" />

    <div class="mb-4">
        <h1>Edit Report</h1>
    </div>

    <form method="post" action="{{ route('saved-reports.update', $savedReport) }}" class="grid max-w-3xl gap-4">
        @csrf
        @method('PUT')
        @include('saved-reports._form')
        <button type="submit" class="inline-flex min-h-11 w-fit items-center rounded bg-secondary px-4 py-2 text-sm font-semibold text-white">Save</button>
    </form>
@endsection
