@extends('layouts.app')

@section('title', 'New Custom Report — '.config('app.name'))

@section('content')
    <div class="mb-4">
        <p class="text-sm"><a href="{{ route('saved-reports.index') }}" class="text-secondary no-underline">Custom Reports</a></p>
        <h1>New Report — {{ $reportTypes[$reportType] ?? ucfirst($reportType) }}</h1>
    </div>

    <form method="post" action="{{ route('saved-reports.store') }}" class="grid max-w-3xl gap-4">
        @csrf
        @include('saved-reports._form', ['savedReport' => null])
        <button type="submit" class="inline-flex min-h-11 w-fit items-center rounded bg-secondary px-4 py-2 text-sm font-semibold text-white">Save &amp; Preview</button>
    </form>
@endsection
