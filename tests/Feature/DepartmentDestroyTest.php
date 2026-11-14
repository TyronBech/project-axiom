<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Department;
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

test('a department can be soft deleted', function () {
    $response = $this->deleteJson(route('departments.destroy', $this->department));

    $response->assertNoContent();
    $this->assertSoftDeleted($this->department);
});

test('deleting a department that is already soft-deleted', function () {
    $this->department->delete();

    $response = $this->deleteJson(route('departments.destroy', $this->department));

    $this->assertSoftDeleted($this->department);
    $response->assertNotFound();
});
