@extends('layouts.app')

@section('title', 'Map Import Fields — '.config('app.name'))

@section('content')
    <div class="mb-4">
        <h1>Map fields — {{ $objectLabel }}</h1>
        <p class="text-sm text-text/70">Match CSV columns to CRM fields, then run the import.</p>
    </div>

    <form method="post" action="{{ route('imports.store') }}" class="max-w-2xl space-y-4 rounded bg-card p-4 shadow-[var(--shadow-card)]">
        @csrf
        <input type="hidden" name="object" value="{{ $object }}">
        <input type="hidden" name="update_or_insert" value="{{ $updateOrInsert ? '1' : '0' }}">

        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="border-b border-black/10 text-left">
                        <th class="px-3 py-2 font-semibold">CRM field</th>
                        <th class="px-3 py-2 font-semibold">CSV column</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($fields as $field => $label)
                        <tr class="border-t border-black/10">
                            <td class="px-3 py-3">{{ $label }}</td>
                            <td class="px-3 py-3">
                                <select name="mapping[{{ $field }}]" class="min-h-11 w-full rounded border border-black/20 bg-card px-3 py-2">
                                    <option value="">— Skip —</option>
                                    @foreach ($headers as $index => $header)
                                        <option value="{{ $index }}" @selected((string) ($mapping[$field] ?? '') === (string) $index)>
                                            {{ $header !== '' ? $header : 'Column '.($index + 1) }}
                                        </option>
                                    @endforeach
                                </select>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="flex flex-wrap gap-2">
            <button type="submit" class="inline-flex min-h-11 items-center rounded bg-secondary px-4 py-2 text-sm font-semibold text-white hover:bg-secondary/90">
                Run import
            </button>
            <a href="{{ route('imports.index') }}" class="inline-flex min-h-11 items-center rounded border border-black/20 bg-card px-4 py-2 text-sm no-underline">Cancel</a>
        </div>
    </form>
@endsection
