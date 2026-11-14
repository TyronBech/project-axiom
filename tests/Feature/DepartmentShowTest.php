<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Department;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);

    $this->department = Department::create([
        'code' => 'HR',
        'name' => 'Human Resources',
    ]);

    $this->user = User::factory()->create([
        'department_id' => $this->department->id,
    ]);

    $this->user->assignRole('department.editor');

    $this->actingAs($this->user);
});

test('test to show a department', function () {
    $response = $this->getJson(route('departments.show', $this->department->id));
    $response->assertOk();
    $response->assertJsonFragment([
        'code' => 'HR',
        'name' => 'Human Resources',
    ]);
});

test('test to show a non-existing department', function () {
    $response = $this->getJson(route('departments.show', $this->department->id + 1));
    $response->assertNotFound();
});
