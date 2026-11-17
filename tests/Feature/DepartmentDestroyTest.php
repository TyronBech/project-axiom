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

test('a department can be soft deleted', function () {
    $finance = Department::create([
        'code' => 'FIN',
        'name' => 'Finance',
    ]);

    $response = $this->deleteJson(route('departments.destroy', $finance));

    $response->assertNoContent();
    $this->assertSoftDeleted($finance);
});

test('deleting a department that is already soft-deleted', function () {
    $department = Department::create([
        'code' => 'FIN',
        'name' => 'Finance',
    ]);

    $department->delete();

    $response = $this->deleteJson(route('departments.destroy', $department));

    $this->assertSoftDeleted($department);
    $response->assertNotFound();
});

test('a department cannot be deleted if it has associated users', function () {
    $department = Department::create([
        'code' => 'FIN',
        'name' => 'Finance',
    ]);

    $user = User::factory()->create([
        'department_id' => $department->id,
    ]);

    $response = $this->deleteJson(route('departments.destroy', $department));

    $response->assertConflict();
    $this->assertDatabaseHas('departments', ['id' => $department->id, 'deleted_at' => null]);
});

test('a user cannot delete a department if they do not have the required permission', function () {
    $this->user->removeRole('department.editor');

    $department = Department::create([
        'code' => 'FIN',
        'name' => 'Finance',
    ]);

    $response = $this->deleteJson(route('departments.destroy', $department));

    $response->assertForbidden();
    $this->assertDatabaseHas('departments', ['id' => $department->id, 'deleted_at' => null]);
});

test('a department can be deleted if it has soft-deleted users', function () {
    $department = Department::create([
        'code' => 'FIN',
        'name' => 'Finance',
    ]);

    $user = User::factory()->create([
        'department_id' => $department->id,
    ]);

    $user->delete();

    $response = $this->deleteJson(route('departments.destroy', $department));

    $response->assertNoContent();
    $this->assertSoftDeleted($department);
    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'department_id' => $department->id,
    ]);
});
