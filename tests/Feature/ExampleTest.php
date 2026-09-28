<?php

test('the home page returns a successful response', function () {
    $response = $this->get(route('home'));

    $response->assertOk();
    $response->assertSee('Home', false);
    $response->assertSee('Leads', false);
});

test('chart and calendar blade components render registration hooks', function () {
    $html = view('components.chart', [
        'type' => 'donut',
        'config' => [
            'labels' => ['A', 'B'],
            'values' => [1, 2],
        ],
    ])->render();

    expect($html)->toContain('data-crm-chart="donut"');

    $calendar = view('components.calendar', [
        'eventsUrl' => '/events.json',
        'view' => 'timeGridWeek',
    ])->render();

    expect($calendar)->toContain('data-crm-calendar');
    expect($calendar)->toContain('data-crm-calendar-events="/events.json"');
    expect($calendar)->toContain('data-crm-calendar-view="timeGridWeek"');
});
