<?php

use App\Models\Department;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Enums\UserStatus;

pest()->uses(RefreshDatabase::class);

beforeEach(function () {
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
});

test('an authorized user can list departments with a bearer token', function () {
    $this->user->assignRole('department.viewer');

    $token = $this->user->createToken('Test Token')->plainTextToken;

    $response = $this->withToken($token)->getJson('api/departments');

    $response->assertOk();
    $response->assertJsonFragment([
        'code' => 'TEST',
        'name' => 'Test Department',
    ]);
});

test('an unauthorized user cannot list departments', function () {
    $response = $this->getJson('api/departments');

    $response->assertUnauthorized();
});

test('an inactive user cannot access departments', function () {
    $this->user->assignRole('department.viewer');
    $token = $this->user->createToken('Test Token')->plainTextToken;
    $this->user->assignRole('department.viewer');

    // Attempt to access the departments endpoint with the inactive user's token
    $this->user->update(['status' => UserStatus::INACTIVE]);

    $response = $this->withToken($token)->getJson('api/departments');

    $response->assertForbidden();
});

test('a user without permission cannot access departments', function () {
    // Do not assign any role to the user
    $token = $this->user->createToken('Test Token')->plainTextToken;

    // Attempt to access the departments endpoint with the user's token
    $response = $this->withToken($token)->getJson('api/departments');

    $response->assertForbidden();
});
