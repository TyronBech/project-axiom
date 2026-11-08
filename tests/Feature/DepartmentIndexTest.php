<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Department;

pest()->use(RefreshDatabase::class);

test('test list of departments', function () {
    Department::create([
        'code' => 'HR',
        'name' => 'Human Resources',
    ]);
    Department::create([
        'code' => 'FIN',
        'name' => 'Finance',
    ]);

    $response = $this->getJson(route('departments.index'));
    $response->assertOk();

    $response->assertJsonFragment([
        'code' => 'HR',
        'name' => 'Human Resources',
    ]);
    $response->assertJsonFragment([
        'code' => 'FIN',
        'name' => 'Finance',
    ]);

    $response->assertJsonCount(2);
});

test('test list of filtered departments with SQL-like syntax', function () {
    Department::create([
        'code' => 'HR',
        'name' => 'Human Resources',
    ]);
    Department::create([
        'code' => 'FIN',
        'name' => 'Finance',
    ]);

    $response = $this->getJson(route('departments.index', ['code' => "HR' OR '1'='1"]));
    $response->assertOk();
    $response->assertJsonCount(0);

});

test('test list of filtered departments with valid code', function () {
    Department::create([
        'code' => 'HR',
        'name' => 'Human Resources',
    ]);
    Department::create([
        'code' => 'FIN',
        'name' => 'Finance',
    ]);

    $response = $this->getJson(route('departments.index', ['code' => "HR"]));
    $response->assertOk();
    $response->assertJsonCount(1);
    $response->assertJsonFragment([
        'code' => 'HR',
        'name' => 'Human Resources',
    ]);
});

test('test list of SQL-like literal value department', function () {
    Department::create([
        'code' => "HR' OR 1=1; --",
        'name' => 'Human Resources',
    ]);

    $response = $this->getJson(route('departments.index', ['code' => "HR' OR 1=1; --"]));
    $response->assertOk();
    $response->assertJsonCount(1);
    $response->assertJsonFragment([
        'code' => "HR' OR 1=1; --",
        'name' => 'Human Resources',
    ]);
});
