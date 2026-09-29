@php
    $definition = old('definition', $savedReport?->definition ?? ['columns' => [array_key_first($fields)], 'filters' => []]);
    $selectedColumns = $definition['columns'] ?? [];
@endphp

<div class="grid gap-3 sm:grid-cols-2">
    <x-form-field name="name" label="Report Name" :value="old('name', $savedReport?->name ?? '')" required />
    <x-form-field name="folder" label="Folder" :value="old('folder', $savedReport?->folder ?? 'private')" />
</div>
<x-form-field name="description" label="Description" type="textarea" :value="old('description', $savedReport?->description ?? '')" />

@if (! isset($savedReport))
    <input type="hidden" name="report_type" value="{{ $reportType }}">
@endif

<fieldset class="rounded border border-black/10 p-4">
    <legend class="px-1 text-sm font-semibold text-primary">Columns</legend>
    <div class="grid gap-2 sm:grid-cols-2">
        @foreach ($fields as $key => $field)
            <label class="flex items-center gap-2 text-sm">
                <input type="checkbox" name="definition[columns][]" value="{{ $key }}" class="size-4" @checked(in_array($key, $selectedColumns, true))>
                {{ $field['label'] }}
            </label>
        @endforeach
    </div>
</fieldset>

<fieldset class="rounded border border-black/10 p-4">
    <legend class="px-1 text-sm font-semibold text-primary">Group By (optional)</legend>
    <x-form-field
        name="definition[group_by]"
        label="Group field"
        :value="old('definition.group_by', $definition['group_by'] ?? '')"
        :options="['' => 'None'] + collect($fields)->filter(fn ($f) => $f['groupable'])->mapWithKeys(fn ($f, $k) => [$k => $f['label']])->all()"
    />
</fieldset>

<fieldset class="rounded border border-black/10 p-4">
    <legend class="px-1 text-sm font-semibold text-primary">Chart (optional)</legend>
    <div class="grid gap-3 sm:grid-cols-2">
        <x-form-field name="definition[chart][type]" label="Chart type" :value="old('definition.chart.type', $definition['chart']['type'] ?? 'bar')" :options="['bar' => 'Bar', 'donut' => 'Donut', 'line' => 'Line']" />
        <x-form-field name="definition[chart][column]" label="Chart column" :value="old('definition.chart.column', $definition['chart']['column'] ?? '')" :options="['' => 'Select…'] + collect($fields)->mapWithKeys(fn ($f, $k) => [$k => $f['label']])->all()" />
    </div>
</fieldset>

<fieldset class="rounded border border-black/10 p-4">
    <legend class="px-1 text-sm font-semibold text-primary">Filter</legend>
    @php $filter = $definition['filters'][0] ?? []; @endphp
    <div class="grid gap-3 sm:grid-cols-3">
        <x-form-field name="definition[filters][0][field]" label="Field" :value="old('definition.filters.0.field', $filter['field'] ?? '')" :options="['' => 'None'] + collect($fields)->filter(fn ($f) => $f['filterable'])->mapWithKeys(fn ($f, $k) => [$k => $f['label']])->all()" />
        <x-form-field name="definition[filters][0][operator]" label="Operator" :value="old('definition.filters.0.operator', $filter['operator'] ?? 'eq')" :options="$filterOperators" />
        <x-form-field name="definition[filters][0][value]" label="Value" :value="old('definition.filters.0.value', $filter['value'] ?? '')" />
    </div>
</fieldset>

<label class="flex items-center gap-2 text-sm">
    <input type="checkbox" name="is_private" value="1" class="size-4" @checked(old('is_private', $savedReport?->is_private ?? true))>
    Private report
</label>
