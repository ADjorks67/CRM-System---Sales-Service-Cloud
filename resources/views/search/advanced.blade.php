@extends('layouts.app')

@section('title', 'Advanced Search — '.config('app.name'))

@section('content')
    <div class="mb-4 flex flex-wrap items-end justify-between gap-3">
        <div>
            <h1>Advanced Search</h1>
            <p class="text-sm text-text/70">Field criteria, AND/OR logic, and date ranges (FR-SRCH-003).</p>
        </div>
        <a href="{{ route('saved-searches.index') }}" class="text-sm text-secondary no-underline">My saved searches</a>
    </div>

    <form method="post" action="{{ route('search.advanced.run') }}" id="advanced-search-form" class="mb-6 space-y-4 rounded bg-card p-4 shadow-[var(--shadow-card)]">
        @csrf
        <div class="grid gap-3 sm:grid-cols-2">
            <label class="block text-sm">
                <span class="mb-1 block font-medium">Object</span>
                <select name="object_type" id="object_type" class="min-h-11 w-full rounded border border-black/20 bg-page px-3 py-2 text-sm" onchange="window.location='{{ route('search.advanced.create') }}?object_type='+this.value">
                    @foreach ($objectLabels as $key => $label)
                        <option value="{{ $key }}" @selected($objectType === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </label>
            <label class="block text-sm">
                <span class="mb-1 block font-medium">Date field</span>
                <select name="date_field" class="min-h-11 w-full rounded border border-black/20 bg-page px-3 py-2 text-sm">
                    @foreach ($fieldCatalog[$objectType] as $field => $meta)
                        @if ($meta['type'] === 'date')
                            <option value="{{ $field }}" @selected(($definition['date_field'] ?? 'created_at') === $field)>{{ $meta['label'] }}</option>
                        @endif
                    @endforeach
                </select>
            </label>
        </div>

        <div class="grid gap-3 sm:grid-cols-2">
            <label class="block text-sm">
                <span class="mb-1 block font-medium">Date from</span>
                <input type="date" name="date_from" value="{{ $definition['date_from'] ?? '' }}" class="min-h-11 w-full rounded border border-black/20 bg-page px-3 py-2 text-sm">
            </label>
            <label class="block text-sm">
                <span class="mb-1 block font-medium">Date to</span>
                <input type="date" name="date_to" value="{{ $definition['date_to'] ?? '' }}" class="min-h-11 w-full rounded border border-black/20 bg-page px-3 py-2 text-sm">
            </label>
        </div>

        <fieldset>
            <legend class="mb-2 text-sm font-semibold text-primary">Conditions</legend>
            <div id="conditions" class="space-y-2">
                @foreach (($definition['conditions'] ?? [['field' => '', 'operator' => 'contains', 'value' => '', 'logic' => 'AND']]) as $i => $condition)
                    <div class="flex flex-wrap items-end gap-2 rounded border border-black/10 p-2">
                        @if ($i > 0)
                            <label class="block text-sm">
                                <span class="mb-1 block font-medium">Logic</span>
                                <select name="conditions[{{ $i }}][logic]" class="min-h-11 rounded border border-black/20 bg-page px-2 py-2 text-sm">
                                    <option value="AND" @selected(($condition['logic'] ?? 'AND') === 'AND')>AND</option>
                                    <option value="OR" @selected(($condition['logic'] ?? '') === 'OR')>OR</option>
                                </select>
                            </label>
                        @else
                            <input type="hidden" name="conditions[{{ $i }}][logic]" value="AND">
                        @endif
                        <label class="block min-w-[10rem] flex-1 text-sm">
                            <span class="mb-1 block font-medium">Field</span>
                            <select name="conditions[{{ $i }}][field]" class="min-h-11 w-full rounded border border-black/20 bg-page px-2 py-2 text-sm">
                                @foreach ($fieldCatalog[$objectType] as $field => $meta)
                                    <option value="{{ $field }}" @selected(($condition['field'] ?? '') === $field)>{{ $meta['label'] }}</option>
                                @endforeach
                            </select>
                        </label>
                        <label class="block min-w-[8rem] text-sm">
                            <span class="mb-1 block font-medium">Operator</span>
                            <select name="conditions[{{ $i }}][operator]" class="min-h-11 w-full rounded border border-black/20 bg-page px-2 py-2 text-sm">
                                @foreach ($operators as $op => $label)
                                    <option value="{{ $op }}" @selected(($condition['operator'] ?? '') === $op)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </label>
                        <label class="block min-w-[10rem] flex-1 text-sm">
                            <span class="mb-1 block font-medium">Value</span>
                            <input type="text" name="conditions[{{ $i }}][value]" value="{{ $condition['value'] ?? '' }}" class="min-h-11 w-full rounded border border-black/20 bg-page px-3 py-2 text-sm">
                        </label>
                    </div>
                @endforeach
            </div>
            <button type="button" id="add-condition" class="mt-2 text-sm text-secondary">+ Add condition</button>
        </fieldset>

        <div class="flex flex-wrap gap-2">
            <button type="submit" class="inline-flex min-h-11 items-center rounded bg-secondary px-4 py-2 text-sm font-semibold text-white">Run search</button>
        </div>
    </form>

    <form method="post" action="{{ route('search.advanced.save') }}" class="mb-6 flex flex-wrap items-end gap-2 rounded bg-card p-4 shadow-[var(--shadow-card)]">
        @csrf
        <input type="hidden" name="object_type" value="{{ $objectType }}">
        <input type="hidden" name="date_field" value="{{ $definition['date_field'] ?? 'created_at' }}">
        <input type="hidden" name="date_from" value="{{ $definition['date_from'] ?? '' }}">
        <input type="hidden" name="date_to" value="{{ $definition['date_to'] ?? '' }}">
        @foreach (($definition['conditions'] ?? []) as $i => $condition)
            <input type="hidden" name="conditions[{{ $i }}][field]" value="{{ $condition['field'] ?? '' }}">
            <input type="hidden" name="conditions[{{ $i }}][operator]" value="{{ $condition['operator'] ?? '' }}">
            <input type="hidden" name="conditions[{{ $i }}][value]" value="{{ $condition['value'] ?? '' }}">
            <input type="hidden" name="conditions[{{ $i }}][logic]" value="{{ $condition['logic'] ?? 'AND' }}">
        @endforeach
        <label class="block min-w-[14rem] flex-1 text-sm">
            <span class="mb-1 block font-medium">Save as</span>
            <input type="text" name="name" required maxlength="120" placeholder="My search" class="min-h-11 w-full rounded border border-black/20 bg-page px-3 py-2 text-sm">
        </label>
        <button type="submit" class="inline-flex min-h-11 items-center rounded border border-black/20 px-4 py-2 text-sm">Save search</button>
    </form>

    @if ($results !== null)
        <section aria-label="Results">
            <h2 class="mb-2 text-base font-semibold text-primary">{{ $results->count() }} result(s)</h2>
            @if ($results->isEmpty())
                <p class="text-sm text-text/70">No matching records.</p>
            @else
                <ul class="divide-y divide-black/10 rounded bg-card shadow-[var(--shadow-card)]">
                    @foreach ($results as $record)
                        @php
                            $url = match ($objectType) {
                                'lead' => route('leads.show', $record),
                                'account' => route('accounts.show', $record),
                                'contact' => route('contacts.show', $record),
                                'opportunity' => route('opportunities.show', $record),
                                'case' => route('cases.show', $record),
                                default => '#',
                            };
                            $label = method_exists($record, 'displayName') ? $record->displayName() : (string) $record->getKey();
                        @endphp
                        <li class="flex flex-wrap items-center justify-between gap-2 px-4 py-3 text-sm">
                            <a href="{{ $url }}" class="font-medium no-underline">{{ $label }}</a>
                            <a href="{{ $url }}" class="text-secondary no-underline">Open</a>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>
    @endif
@endsection

@push('scripts')
<script>
(() => {
    const container = document.getElementById('conditions');
    const addBtn = document.getElementById('add-condition');
    if (!container || !addBtn) return;
    const fields = @json($fieldCatalog[$objectType]);
    const operators = @json($operators);
    addBtn.addEventListener('click', () => {
        const index = container.children.length;
        const fieldOptions = Object.entries(fields).map(([k, v]) => `<option value="${k}">${v.label}</option>`).join('');
        const opOptions = Object.entries(operators).map(([k, v]) => `<option value="${k}">${v}</option>`).join('');
        const row = document.createElement('div');
        row.className = 'flex flex-wrap items-end gap-2 rounded border border-black/10 p-2';
        row.innerHTML = `
            <label class="block text-sm"><span class="mb-1 block font-medium">Logic</span>
            <select name="conditions[${index}][logic]" class="min-h-11 rounded border border-black/20 bg-page px-2 py-2 text-sm">
                <option value="AND">AND</option><option value="OR">OR</option>
            </select></label>
            <label class="block min-w-[10rem] flex-1 text-sm"><span class="mb-1 block font-medium">Field</span>
            <select name="conditions[${index}][field]" class="min-h-11 w-full rounded border border-black/20 bg-page px-2 py-2 text-sm">${fieldOptions}</select></label>
            <label class="block min-w-[8rem] text-sm"><span class="mb-1 block font-medium">Operator</span>
            <select name="conditions[${index}][operator]" class="min-h-11 w-full rounded border border-black/20 bg-page px-2 py-2 text-sm">${opOptions}</select></label>
            <label class="block min-w-[10rem] flex-1 text-sm"><span class="mb-1 block font-medium">Value</span>
            <input type="text" name="conditions[${index}][value]" class="min-h-11 w-full rounded border border-black/20 bg-page px-3 py-2 text-sm"></label>`;
        container.appendChild(row);
    });
})();
</script>
@endpush
