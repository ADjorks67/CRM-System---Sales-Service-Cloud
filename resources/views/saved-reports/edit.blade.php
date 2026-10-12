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
        <button type="submit" class="crm-btn-primary">Save</button>
    </form>
@endsection
