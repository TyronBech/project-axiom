<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Department;
use App\Models\User;

pest()->use(RefreshDatabase::class);

test('a department can be updated', function () {
    $department = Department::create([
        'code' => 'HR',
        'name' => 'Human Resources',
    ]);

    $user = User::factory()->create([
        'department_id' => $department->id,
    ]);

    $this->actingAs($user);

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

    $user = User::factory()->create([
        'department_id' => $department->id,
    ]);

    $this->actingAs($user);

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

test('an update ignores an unvalidated code', function () {
    $department = Department::create([
        'code' => 'HR',
        'name' => 'Human Resources',
    ]);

    $user = User::factory()->create([
        'department_id' => $department->id,
    ]);

    $this->actingAs($user);

    $response = $this->patchJson(route('departments.update', $department), [
        'code' => 'HRD',
        'name' => 'Human Resource Development',
    ]);

    $response->assertOk();
    $this->assertDatabaseHas('departments', [
        'id' => $department->id,
        'code' => 'HR',
        'name' => 'Human Resource Development',
    ]);
});

test('a department update fails with unauthenticated user', function () {
    $department = Department::create([
        'code' => 'HR',
        'name' => 'Human Resources',
    ]);

    $response = $this->patchJson(route('departments.update', $department), [
        'name' => 'People Operations'
    ]);

    $response->assertUnauthorized();
    $this->assertDatabaseHas('departments', [
        'id' => $department->id,
        'code' => 'HR',
        'name' => 'Human Resources',
    ]);
});

test('a user can only update its own department', function () {
    $humanResources = Department::create([
        'code' => 'HR',
        'name' => 'Human Resources',
    ]);

    $finance = Department::create([
        'code' => 'FIN',
        'name' => 'Finance',
    ]);

    $user = User::factory()->create([
        'department_id' => $humanResources->id,
    ]);

    $this->actingAs($user);

    $response = $this->patchJson(route('departments.update', $finance), [
        'name' => 'Financial Operations'
    ]);

    $response->assertForbidden();
    $this->assertDatabaseHas('departments', [
        'id' => $finance->id,
        'code' => 'FIN',
        'name' => 'Finance',
    ]);
});
