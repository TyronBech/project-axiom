<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Department;

pest()->use(RefreshDatabase::class);

test('the departments table starts empty', function () {
    $this->assertDatabaseCount('departments', 0);
});

test('a department has been created', function () {
    Department::create([
        'code' => 'IT',
        'name' => 'Information Technology',
    ]);

    $this->assertDatabaseHas('departments', [
        'code' => 'IT',
        'name' => 'Information Technology',
    ]);
});

test('a department has active status by default', function () {
    Department::create([
        'code' => 'HR',
        'name' => 'Human Resources',
    ]);

    $this->assertDatabaseHas('departments', [
        'code' => 'HR',
        'name' => 'Human Resources',
        'status' => 'active',
    ]);
});

test('a department can be soft deleted', function () {
    $department = Department::create([
        'code' => 'FIN',
        'name' => 'Finance',
    ]);

    $department->delete();

    $this->assertSoftDeleted('departments', [
        'code' => 'FIN',
        'name' => 'Finance',
    ]);
});

test('a department can still exist in soft deleted state', function () {
    $department = Department::create([
        'code' => 'MKT',
        'name' => 'Marketing',
    ]);
    $department->delete();
    $value = Department::find($department->id);
    $trashedDepartment = Department::withTrashed()->find($department->id);

    expect($value)->toBeNull();
    expect($trashedDepartment->id)->toBe($department->id);
});

test('a department can update values', function () {
    $department = Department::create([
        'code' => 'OPS',
        'name' => 'Operations',
    ]);

    $department->update([
        'name' => 'Operations and Logistics',
    ]);

    $this->assertDatabaseHas('departments', [
        'code' => 'OPS',
        'name' => 'Operations and Logistics',
    ]);
});
