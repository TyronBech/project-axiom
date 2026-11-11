<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Department;

pest()->use(RefreshDatabase::class);

test('a department can be soft deleted', function () {
    $department = Department::create([
        'code' => 'HR',
        'name' => 'Human Resources',
    ]);

    $response = $this->deleteJson(route('departments.destroy', $department));

    $response->assertNoContent();
    $this->assertSoftDeleted($department);
});

test('deleting a department that is already soft-deleted', function () {
    $department = Department::create([
        'code' => 'HR',
        'name' => 'Human Resources',
    ]);

    $department->delete();

    $response = $this->deleteJson(route('departments.destroy', $department));
    
    $this->assertSoftDeleted($department);
    $response->assertNotFound();
});
