<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Department;

pest()->use(RefreshDatabase::class);

test('test to show a department', function () {
    $department = Department::create([
        'code' => 'HR',
        'name' => 'Human Resources',
    ]);

    $response = $this->getJson(route('departments.show', $department->id));
    $response->assertOk();
    $response->assertJsonFragment([
        'code' => 'HR',
        'name' => 'Human Resources',
    ]);
});

test('test to show a non-existing department', function () {
    $department = Department::create([
        'code' => 'HR',
        'name' => 'Human Resources',
    ]);

    $response = $this->getJson(route('departments.show', $department->id + 1));
    $response->assertNotFound();
});
