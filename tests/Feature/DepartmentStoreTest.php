<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

test('a department can be created', function () {
    $response = $this->postJson(route('departments.store'), [
        'code' => 'HR',
        'name' => 'Human Resources',
    ]);

    $response->assertCreated();
    $response->assertJsonFragment([
        'code' => 'HR',
        'name' => 'Human Resources',
    ]);

    $this->assertDatabaseHas('departments', [
        'code' => 'HR',
        'name' => 'Human Resources',
    ]);
});

test('a department requires a code and name', function () {
    $response = $this->postJson(route('departments.store'), []);

    $response->assertUnprocessable();
    $response->assertJsonValidationErrors(['code', 'name']);
    $this->assertDatabaseCount('departments', 0);
});

test('a department code must be unique', function () {
    $response = $this->postJson(route('departments.store'), [
        'code' => 'HR',
        'name' => 'Human Resources',
    ]);

    $response->assertCreated();

    $response = $this->postJson(route('departments.store'), [
        'code' => 'HR',
        'name' => 'Information Technology',
    ]);

    $response->assertUnprocessable();
    $response->assertJsonValidationErrors(['code']);
    $this->assertDatabaseCount('departments', 1);
});

test('a department status cannot be null', function () {
    $response = $this->postJson(route('departments.store'), [
        'code' => 'HR',
        'name' => 'Human Resources',
        'status' => null,
    ]);

    $response->assertUnprocessable();
    $response->assertJsonValidationErrors(['status']);
    $this->assertDatabaseCount('departments', 0);
});

test('a department status must be either active or inactive', function () {
    $response = $this->postJson(route('departments.store'), [
        'code' => 'HR',
        'name' => 'Human Resources',
        'status' => 'archived',
    ]);

    $response->assertUnprocessable();
    $response->assertJsonValidationErrors(['status']);
    $this->assertDatabaseCount('departments', 0);
});

test('SQL-like department codes are treated as literal values', function () {
    $existingResponse = $this->postJson(route('departments.store'), [
        'code' => "HR",
        'name' => 'Human Resources',
    ]);
    $sqlLikeResponse = $this->postJson(route('departments.store'), [
        'code' => "HR' OR 1=1; --",
        'name' => 'Human Resources',
    ]);

    $existingResponse->assertCreated();
    $sqlLikeResponse->assertCreated();
    $this->assertDatabaseHas('departments', [
        'code' => "HR",
        'name' => 'Human Resources',
    ]);
    $this->assertDatabaseHas('departments', [
        'code' => "HR' OR 1=1; --",
        'name' => 'Human Resources',
    ]);
    $this->assertDatabaseCount('departments', 2);
});
