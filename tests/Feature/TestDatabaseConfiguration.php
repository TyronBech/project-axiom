<?php

test('the default test database is in-memory sqlite', function () {
    expect(config('database.default'))->toBe('sqlite');
    expect(config('database.connections.sqlite.driver'))->toBe('sqlite');
    expect(config('database.connections.sqlite.database'))->toBe(':memory:');
    expect(in_array(config('database.connections.sqlite.url'), [null, ''], true))
    ->toBeTrue();
});
