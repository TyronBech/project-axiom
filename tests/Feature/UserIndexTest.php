<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Database\Seeders\RolePermissionSeeder;
use App\Models\User;
use App\Models\Department;

pest()->uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);

    $this->department = Department::create([
        'code' => 'TEST',
        'name' => 'Test Department',
    ]);


    $this->user = User::create([
        'department_id' => $this->department->id,
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => bcrypt('password'),
    ]);

    $this->user->refresh();
});

test('an authenticated user can view the list of users', function () {
    $this->user->assignRole('user.admin');

    $token = $this->user->createToken('test-token')->plainTextToken;

    $response = $this->withToken($token)->getJson(route('users.index'));

    $response->assertOk();
    $response->assertJsonFragment([
        'id' => $this->user->id,
        'name' => $this->user->name,
        'email' => $this->user->email,
    ]);
});

test('an unauthenticated user cannot view the list of users', function () {
    $response = $this->getJson(route('users.index'));

    $response->assertUnauthorized();
    $response->assertJson([
        'message' => 'Unauthenticated.',
    ]);
});

test('an unauthorized user cannot view the list of users', function () {
    $token = $this->user->createToken('test-token')->plainTextToken;

    $response = $this->withToken($token)->getJson(route('users.index'));

    $response->assertForbidden();
    $response->assertJson([
        'message' => 'This action is unauthorized.',
    ]);
});

test('an inactive user cannot view the list of users', function () {
    $this->user->assignRole('user.admin');
    $this->user->update(['status' => 'inactive']);

    $token = $this->user->createToken('test-token')->plainTextToken;

    $response = $this->withToken($token)->getJson(route('users.index'));

    $response->assertForbidden();
    $response->assertJson([
        'message' => 'Your account is inactive. Please contact support.',
    ]);
});

test('a user can search for list of valid users', function () {
    $this->user->assignRole('user.admin');

    $token = $this->user->createToken('test-token')->plainTextToken;

    User::create([
        'department_id' => $this->department->id,
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => bcrypt('password'),
    ]);

    User::create([
        'department_id' => $this->department->id,
        'name' => 'John Smith',
        'email' => 'johnsmith@example.com',
        'password' => bcrypt('password'),
    ]);

    $response = $this->withToken($token)->getJson(route('users.index', ['name' => 'John']));

    $response->assertOk();
    $response->assertJsonCount(2);
    $response->assertJsonFragment([
        'name' => 'John Doe',
    ]);
    $response->assertJsonFragment([
        'name' => 'John Smith',
    ]);
});

test('a user cannot search for list with empty search', function () {
    $this->user->assignRole('user.admin');

    $token = $this->user->createToken('test-token')->plainTextToken;

    $response = $this->withToken($token)->getJson(route('users.index', ['name' => '']));

    $response->assertUnprocessable();
    $response->assertJsonValidationErrors(['name']);
});

test('a user cannot search for list with SQL injection attempt', function () {
    $this->user->assignRole('user.admin');

    $token = $this->user->createToken('test-token')->plainTextToken;

    $response = $this->withToken($token)->getJson(route('users.index', ['name' => "' OR '1'='1"]));

    $response->assertOk();
    $response->assertJsonCount(0);
});

test('a user can search for list of users based on department', function () {
    $this->user->assignRole('user.admin');

    $token = $this->user->createToken('test-token')->plainTextToken;

    $department2 = Department::create([
        'code' => 'TEST2',
        'name' => 'Test Department 2',
    ]);

    User::create([
        'department_id' => $department2->id,
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
        'password' => bcrypt('password'),
    ]);

    $response = $this->withToken($token)->getJson(route('users.index', ['department_id' => $department2->id]));

    $response->assertOk();
    $response->assertJsonCount(1);
    $response->assertJsonFragment([
        'name' => 'Jane Doe',
    ]);
});

test('a soft-deleted user cannot be viewed in the list of users', function () {
    $this->user->assignRole('user.admin');

    $token = $this->user->createToken('test-token')->plainTextToken;

    $softDeletedUser = User::create([
        'department_id' => $this->department->id,
        'name' => 'Soft Deleted User',
        'email' => 'softdeleted@example.com',
        'password' => bcrypt('password'),
    ]);

    $softDeletedUser->delete();

    $response = $this->withToken($token)->getJson(route('users.index', ['name' => 'Soft Deleted User']));

    $response->assertOk();
    $response->assertJsonCount(0);
    $response->assertJsonMissing([
        'id' => $softDeletedUser->id,
        'name' => $softDeletedUser->name,
        'email' => $softDeletedUser->email,
    ]);
});

test('a user\'s password and remember_token are hidden in the list of users', function () {
    $this->user->assignRole('user.admin');

    $token = $this->user->createToken('test-token')->plainTextToken;

    $response = $this->withToken($token)->getJson(route('users.index'));

    $response->assertOk();
    $response->assertJsonMissing([
        'password' => $this->user->password,
        'remember_token' => $this->user->remember_token,
    ]);
});
