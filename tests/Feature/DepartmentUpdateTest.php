<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Department;

pest()->use(RefreshDatabase::class);

test('a department can be updated', function () {
    $department = Department::create([
        'code' => 'HR',
        'name' => 'Human Resources',
    ]);

    $response = $this->patchJson(route('departments.update', $department), [
        'name' => 'People Operations'
    ]);
    $response->assertOk();
    $this->assertDatabaseHas('departments', [
        'id' => $department->id,
        'code' => 'HR',
        'name' => 'People Operations',
    ]);
    $response->assertJsonFragment([
        'code' => 'HR',
        'name' => 'People Operations',
    ]);
});

test('a department update request fails with empty name', function () {
    $department = Department::create([
        'code' => 'HR',
        'name' => 'Human Resources',
    ]);

    $response = $this->patchJson(route('departments.update', $department), [
        'name' => '',
    ]);

    $response->assertUnprocessable();
    $response->assertJsonValidationErrors(['name']);
    $this->assertDatabaseHas('departments', [
        'id' => $department->id,
        'code' => 'HR',
        'name' => 'Human Resources',
    ]);
});
