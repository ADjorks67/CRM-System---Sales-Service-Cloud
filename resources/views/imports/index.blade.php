@extends('layouts.app')

@section('title', 'Import Data — '.config('app.name'))

@section('content')
    <div class="mb-4 flex flex-wrap items-end justify-between gap-3">
        <div>
            <h1>Import Data</h1>
            <p class="text-sm text-muted">CSV import with field mapping for Leads, Accounts, Contacts, and Opportunities.</p>
        </div>
        <a href="{{ route('exports.index') }}" class="crm-btn-secondary">Export CSV</a>
    </div>

    @if (session('import_error_count'))
        <div class="mb-4 rounded border border-amber-600/30 bg-amber-50 px-4 py-3 text-sm">
            {{ session('import_error_count') }} row(s) failed.
            <a href="{{ route('imports.errors') }}" class="font-medium text-secondary">Download error report</a>
        </div>
    @endif

    <form method="post" action="{{ route('imports.preview') }}" enctype="multipart/form-data" class="max-w-xl space-y-4 crm-card p-4 sm:p-5">
        @csrf
        <x-form-field name="object" label="Object" :options="$objects" />
        <div>
            <label for="file" class="mb-1 block text-sm font-medium">CSV file</label>
            <input id="file" type="file" name="file" accept=".csv,text/csv" required class="block w-full text-sm">
            @error('file')
                <p class="mt-1 text-sm text-red-700">{{ $message }}</p>
            @enderror
        </div>
        <label class="flex items-center gap-2 text-sm">
            <input type="checkbox" name="update_or_insert" value="1" class="size-4 rounded border-border" @checked(old('update_or_insert', true))>
            Update existing records when a match is found
        </label>
        <button type="submit" class="crm-btn-primary">
            Continue to mapping
        </button>
    </form>
@endsection
