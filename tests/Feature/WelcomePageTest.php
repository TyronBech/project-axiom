<?php

test('checking the welcome page and it\'s content', function () {
    $response = $this->get('/');

    $response->assertOk();
    $response->assertSee('Example test for feature testing');
});
