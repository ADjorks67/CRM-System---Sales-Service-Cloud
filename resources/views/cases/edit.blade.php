@extends('layouts.app')

@section('title', 'Edit Case — '.config('app.name'))

@section('content')
    <h1 class="mb-4">Edit Case</h1>

    <form method="post" action="{{ route('cases.update', $crmCase) }}" class="max-w-4xl space-y-4 rounded bg-card p-6 shadow-[var(--shadow-card)]">
        @csrf
        @method('PUT')
        @include('cases._form', ['crmCase' => $crmCase])

        <div class="flex flex-wrap gap-2 pt-2">
            <button type="submit" name="save_action" value="save" class="inline-flex min-h-11 items-center rounded bg-secondary px-4 py-2 text-sm font-semibold text-white">Save</button>
            <button type="submit" name="save_action" value="save_new" class="inline-flex min-h-11 items-center rounded border border-secondary px-4 py-2 text-sm font-semibold text-secondary">Save &amp; New</button>
            <a href="{{ route('cases.show', $crmCase) }}" class="inline-flex min-h-11 items-center rounded border border-black/20 px-4 py-2 text-sm text-text no-underline">Cancel</a>
        </div>
    </form>
@endsection
