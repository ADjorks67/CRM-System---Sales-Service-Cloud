@extends('layouts.app')

@section('title', 'New Custom Report — '.config('app.name'))

@section('content')
    <x-breadcrumbs :items="[
        ['label' => 'Custom Reports', 'url' => route('saved-reports.index')],
        ['label' => 'New Report'],
    ]" />

    <div class="mb-4">
        <h1>New Report — {{ $reportTypes[$reportType] ?? ucfirst($reportType) }}</h1>
    </div>

    <form method="post" action="{{ route('saved-reports.store') }}" class="grid max-w-3xl gap-4">
        @csrf
        @include('saved-reports._form', ['savedReport' => null])
        <button type="submit" class="inline-flex min-h-11 w-fit items-center rounded bg-secondary px-4 py-2 text-sm font-semibold text-white">Save &amp; Preview</button>
    </form>
@endsection
