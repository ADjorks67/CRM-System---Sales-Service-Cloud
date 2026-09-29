<?php

test('health endpoint reports ok when database is reachable', function () {
    $this->getJson(route('health'))
        ->assertOk()
        ->assertJsonPath('status', 'ok')
        ->assertJsonPath('checks.app', 'ok')
        ->assertJsonPath('checks.database', 'ok');
});

test('laravel up endpoint remains available', function () {
    $this->get('/up')->assertOk();
});
