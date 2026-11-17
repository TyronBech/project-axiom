<?php

use App\Models\Department;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

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

    $this->user->refresh();

    $this->user->assignRole('department.editor');

    $this->actingAs($this->user);
});

test('a department can be updated', function () {
    $response = $this->patchJson(route('departments.update', $this->department), [
        'name' => 'People Operations',
    ]);
    $response->assertOk();
    $this->assertDatabaseHas('departments', [
        'id' => $this->department->id,
        'code' => 'HR',
        'name' => 'People Operations',
    ]);
    $response->assertJsonFragment([
        'code' => 'HR',
        'name' => 'People Operations',
    ]);
});

test('a department update request fails with empty name', function () {
    $response = $this->patchJson(route('departments.update', $this->department), [
        'name' => '',
    ]);

    $response->assertUnprocessable();
    $response->assertJsonValidationErrors(['name']);
    $this->assertDatabaseHas('departments', [
        'id' => $this->department->id,
        'code' => 'HR',
        'name' => 'Human Resources',
    ]);
});

test('a department code can be updated on its own', function () {
    $response = $this->patchJson(route('departments.update', $this->department), [
        'code' => 'HRD',
    ]);

    $response->assertOk();
    $this->assertDatabaseHas('departments', [
        'id' => $this->department->id,
        'code' => 'HRD',
        'name' => 'Human Resources',
    ]);
});

test('a department update fails with unauthenticated user', function () {
    auth()->logout();
    $response = $this->patchJson(route('departments.update', $this->department), [
        'name' => 'People Operations',
    ]);

    $response->assertUnauthorized();
    $this->assertDatabaseHas('departments', [
        'id' => $this->department->id,
        'code' => 'HR',
        'name' => 'Human Resources',
    ]);
});

test('a user can only update its own department', function () {
    $finance = Department::create([
        'code' => 'FIN',
        'name' => 'Finance',
    ]);
    $response = $this->patchJson(route('departments.update', $finance), [
        'name' => 'Financial Operations',
    ]);

    $response->assertForbidden();
    $this->assertDatabaseHas('departments', [
        'id' => $finance->id,
        'code' => 'FIN',
        'name' => 'Finance',
    ]);
});

test('a department viewer cannot update their own department', function () {
    $this->user->syncRoles('department.viewer');
    $response = $this->patchJson(route('departments.update', $this->department), [
        'name' => 'People Operations',
    ]);

    $response->assertForbidden();
    $this->assertDatabaseHas('departments', [
        'id' => $this->department->id,
        'code' => 'HR',
        'name' => 'Human Resources',
    ]);
});

test('a user with multiple roles can update their own department', function () {
    $this->user->syncRoles(['department.viewer', 'department.editor']);
    $response = $this->patchJson(route('departments.update', $this->department), [
        'name' => 'People Operations',
    ]);

    $response->assertOk();
    $this->assertDatabaseHas('departments', [
        'id' => $this->department->id,
        'code' => 'HR',
        'name' => 'People Operations',
    ]);
});

test('a user can update code, name, and status of their own department', function () {
    $response = $this->patchJson(route('departments.update', $this->department), [
        'code' => 'HRD',
        'name' => 'Human Resource Development',
        'status' => 'inactive',
    ]);

    $response->assertOk();
    $this->assertDatabaseHas('departments', [
        'id' => $this->department->id,
        'code' => 'HRD',
        'name' => 'Human Resource Development',
        'status' => 'inactive',
    ]);
});

test('a department update fails with the same code as another department', function () {
    $finance = Department::create([
        'code' => 'FIN',
        'name' => 'Finance',
    ]);
    $response = $this->patchJson(route('departments.update', $this->department), [
        'code' => 'FIN',
        'name' => 'Human Resource Development',
    ]);

    $response->assertUnprocessable();
    $response->assertJsonValidationErrors(['code']);
    $this->assertDatabaseHas('departments', [
        'id' => $this->department->id,
        'code' => 'HR',
        'name' => 'Human Resources',
    ]);
});

test('a department cannot be updated with invalid status', function () {
    $response = $this->patchJson(route('departments.update', $this->department), [
        'status' => 'archived',
    ]);

    $response->assertUnprocessable();
    $response->assertJsonValidationErrors(['status']);
    $this->assertDatabaseHas('departments', [
        'id' => $this->department->id,
        'code' => 'HR',
        'name' => 'Human Resources',
        'status' => 'active',
    ]);
});

test('a department can be updated with its own code', function () {
    $response = $this->patchJson(route('departments.update', $this->department), [
        'code' => 'HR',
    ]);

    $response->assertOk();
    $this->assertDatabaseHas('departments', [
        'id' => $this->department->id,
        'code' => 'HR',
    ]);
});
