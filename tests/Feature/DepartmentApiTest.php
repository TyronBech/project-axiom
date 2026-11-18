<?php

use App\Models\Department;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\Fluent\AssertableJson;

pest()->uses(RefreshDatabase::class);

test('an authorized user can list departments with a bearer token', function () {
    $this->department = Department::create([
        'name' => 'Test Department',
        'code' => 'TEST',
    ]);

    $this->user = User::factory()->create([
        'department_id' => $this->department->id,
        'password' => bcrypt('password'),
    ]);

    $this->user->refresh();

    $seeder = new RolePermissionSeeder();
    $seeder->run();

    $this->user->assignRole('department.viewer');

    $token = $this->user->createToken('Test Token')->plainTextToken;

    $response = $this->withToken($token)->getJson('api/departments');

    $response->assertOk();
    $response->assertJsonFragment([
        'code' => 'TEST',
        'name' => 'Test Department',
    ]);
});
