<?php

test('the application shows 404 for missing pages', function () {
    $response = $this->get('/this-page-does-not-exist');

    $response->assertNotFound();
});
