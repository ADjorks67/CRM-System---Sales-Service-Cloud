@extends('layouts.app')

@section('title', 'Edit '.$savedReport->name.' — '.config('app.name'))

@section('content')
    <div class="mb-4">
        <p class="text-sm"><a href="{{ route('saved-reports.show', $savedReport) }}" class="text-secondary no-underline">Back to report</a></p>
        <h1>Edit Report</h1>
    </div>

    <form method="post" action="{{ route('saved-reports.update', $savedReport) }}" class="grid max-w-3xl gap-4">
        @csrf
        @method('PUT')
        @include('saved-reports._form')
        <button type="submit" class="inline-flex min-h-11 w-fit items-center rounded bg-secondary px-4 py-2 text-sm font-semibold text-white">Save</button>
    </form>
@endsection
