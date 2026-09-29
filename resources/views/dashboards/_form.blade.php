@php
    $defaultWidget = [
        'title' => 'Pipeline',
        'widget_type' => 'table',
        'source_type' => 'prebuilt',
        'source_key' => 'all-pipeline-current-year',
        'saved_report_id' => '',
        'grid_x' => 0,
        'grid_y' => 0,
        'grid_w' => 6,
        'grid_h' => 4,
    ];

    $widgets = old('widgets');
    if (! is_array($widgets) || $widgets === []) {
        $widgets = $dashboard?->widgets?->map(fn ($w) => [
            'title' => $w->title,
            'widget_type' => $w->widget_type,
            'source_type' => $w->source_type,
            'source_key' => $w->source_key,
            'saved_report_id' => $w->saved_report_id,
            'grid_x' => $w->grid_x,
            'grid_y' => $w->grid_y,
            'grid_w' => $w->grid_w,
            'grid_h' => $w->grid_h,
        ])->values()->all() ?? [$defaultWidget];
    }

    $prebuiltOptions = ['' => 'Select…'] + $prebuiltReports->mapWithKeys(fn ($r) => [$r['key'] => $r['title']])->all();
    $savedOptions = ['' => 'None'] + $savedReports->mapWithKeys(fn ($r) => [$r->id => $r->name])->all();
@endphp

<div class="grid gap-3 sm:grid-cols-2">
    <x-form-field name="name" label="Dashboard Name" :value="old('name', $dashboard?->name ?? '')" required />
    <x-form-field name="folder" label="Folder" :value="old('folder', $dashboard?->folder ?? 'private')" />
</div>
<div class="grid gap-3 sm:grid-cols-2">
    <x-form-field
        name="refresh_interval_minutes"
        label="Auto-refresh"
        :value="old('refresh_interval_minutes', $dashboard?->refresh_interval_minutes ?? '')"
        :options="['' => 'Off', 5 => '5 minutes', 10 => '10 minutes', 30 => '30 minutes', 60 => '60 minutes']"
    />
</div>
<x-form-field name="description" label="Description" type="textarea" :value="old('description', $dashboard?->description ?? '')" />

<div class="space-y-3" data-dashboard-widgets data-max-widgets="20">
    <div class="flex flex-wrap items-center justify-between gap-2">
        <h2 class="text-base font-semibold text-primary">Widgets <span class="text-sm font-normal text-text/60">(max 20)</span></h2>
        <button type="button" class="inline-flex min-h-11 items-center rounded border border-black/20 px-3 py-2 text-sm" data-add-widget title="Add widget" aria-label="Add widget">
            Add widget
        </button>
    </div>

    <div class="space-y-3" data-widget-list>
        @foreach ($widgets as $index => $widget)
            @include('dashboards._widget-fields', [
                'index' => $index,
                'widget' => $widget,
                'widgetTypes' => $widgetTypes,
                'prebuiltOptions' => $prebuiltOptions,
                'savedOptions' => $savedOptions,
            ])
        @endforeach
    </div>
</div>

<template id="dashboard-widget-template">
    @include('dashboards._widget-fields', [
        'index' => '__INDEX__',
        'widget' => $defaultWidget,
        'widgetTypes' => $widgetTypes,
        'prebuiltOptions' => $prebuiltOptions,
        'savedOptions' => $savedOptions,
    ])
</template>

<label class="flex items-center gap-2 text-sm">
    <input type="checkbox" name="is_private" value="1" class="size-4" @checked(old('is_private', $dashboard?->is_private ?? true))>
    Private dashboard
</label>

@once
    @push('scripts')
        <script>
            (() => {
                const root = document.querySelector('[data-dashboard-widgets]');
                if (! root) return;

                const list = root.querySelector('[data-widget-list]');
                const addBtn = root.querySelector('[data-add-widget]');
                const template = document.getElementById('dashboard-widget-template');
                const max = Number(root.getAttribute('data-max-widgets') || 20);

                const reindex = () => {
                    [...list.querySelectorAll('[data-widget-row]')].forEach((row, index) => {
                        row.querySelectorAll('[name]').forEach((el) => {
                            el.name = el.name.replace(/widgets\[\d+|__INDEX__]/g, `widgets[${index}]`);
                            if (el.id) {
                                el.id = el.id.replace(/widgets_\d+|widgets___INDEX__/g, `widgets_${index}`);
                            }
                        });
                        row.querySelectorAll('label[for]').forEach((label) => {
                            label.htmlFor = label.htmlFor.replace(/widgets_\d+|widgets___INDEX__/g, `widgets_${index}`);
                        });
                        const title = row.querySelector('[data-widget-heading]');
                        if (title) title.textContent = `Widget ${index + 1}`;
                        const y = row.querySelector('[data-grid-y]');
                        if (y) y.value = String(index);
                    });
                    addBtn.disabled = list.querySelectorAll('[data-widget-row]').length >= max;
                };

                addBtn?.addEventListener('click', () => {
                    if (list.querySelectorAll('[data-widget-row]').length >= max) return;
                    const html = template.innerHTML.replaceAll('__INDEX__', String(list.children.length));
                    list.insertAdjacentHTML('beforeend', html);
                    reindex();
                });

                list.addEventListener('click', (event) => {
                    const btn = event.target.closest('[data-remove-widget]');
                    if (! btn) return;
                    const row = btn.closest('[data-widget-row]');
                    if (! row) return;
                    if (list.querySelectorAll('[data-widget-row]').length <= 1) return;
                    row.remove();
                    reindex();
                });

                reindex();
            })();
        </script>
    @endpush
@endonce
