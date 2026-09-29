@php
    /** @var int|string $index */
    $widget = $widget ?? [];
@endphp

<fieldset class="rounded border border-black/10 p-4" data-widget-row>
    <div class="mb-3 flex items-center justify-between gap-2">
        <legend class="px-0 text-sm font-semibold text-primary" data-widget-heading>Widget {{ is_numeric($index) ? ((int) $index + 1) : '' }}</legend>
        <button type="button" class="text-sm text-error underline" data-remove-widget>Remove</button>
    </div>
    <div class="grid gap-3 sm:grid-cols-2">
        <x-form-field :name="'widgets['.$index.'][title]'" label="Title" :value="$widget['title'] ?? 'Widget'" />
        <x-form-field :name="'widgets['.$index.'][widget_type]'" label="Type" :value="$widget['widget_type'] ?? 'table'" :options="$widgetTypes" />
        <x-form-field :name="'widgets['.$index.'][source_type]'" label="Source" :value="$widget['source_type'] ?? 'prebuilt'" :options="['prebuilt' => 'Pre-built report', 'saved_report' => 'Saved report']" />
        <x-form-field :name="'widgets['.$index.'][source_key]'" label="Pre-built key" :value="$widget['source_key'] ?? ''" :options="$prebuiltOptions" />
        <x-form-field :name="'widgets['.$index.'][saved_report_id]'" label="Saved report" :value="$widget['saved_report_id'] ?? ''" :options="$savedOptions" />
        <x-form-field :name="'widgets['.$index.'][grid_w]'" label="Width (cols 1–12)" type="number" :value="$widget['grid_w'] ?? 6" min="1" max="12" />
        <x-form-field :name="'widgets['.$index.'][grid_h]'" label="Height (rows)" type="number" :value="$widget['grid_h'] ?? 4" min="1" max="12" />
        <x-form-field :name="'widgets['.$index.'][grid_x]'" label="Column start (0–11)" type="number" :value="$widget['grid_x'] ?? 0" min="0" max="11" />
        <input type="hidden" name="widgets[{{ $index }}][grid_y]" value="{{ $widget['grid_y'] ?? $index }}" data-grid-y>
    </div>
</fieldset>
