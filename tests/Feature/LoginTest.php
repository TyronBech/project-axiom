<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Department;
use Database\Seeders\RolePermissionSeeder;

pest()->uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);

    $this->department = Department::create([
        'name' => 'Test Department',
        'code' => 'TEST',
    ]);

    $this->user = User::factory()->create([
        'department_id' => $this->department->id,
        'password' => bcrypt('password'),
    ]);

    $this->user->assignRole('user.admin');
});

test('a user can log in', function () {
    $response = $this->postJson('api/login', [
        'email' => $this->user->email,
        'password' => 'password',
        'device_name' => 'Postman',
    ]);

    $response->assertOk();
    $response->assertJsonStructure([
        'token',
    ]);
});

test('a user cannot log in with invalid credentials', function () {
    $response = $this->postJson('api/login', [
        'email' => $this->user->email,
        'password' => 'wrong-password',
        'device_name' => 'Postman',
    ]);

    $response->assertUnauthorized();
    $response->assertJson([
        'message' => 'The provided credentials are incorrect.',
    ]);
    $this->assertDatabaseCount('personal_access_tokens', 0);
});

test('a user cannot login with empty credentials', function () {
    $response = $this->postJson('api/login', [
        'email' => '',
        'password' => '',
        'device_name' => '',
    ]);

    $response->assertUnprocessable();
    $response->assertJsonValidationErrors(['email', 'password', 'device_name']);
    $this->assertDatabaseCount('personal_access_tokens', 0);
});

test('an authenticated user can access the /user endpoint', function () {
    $response = $this->postJson('api/login', [
        'email' => $this->user->email,
        'password' => 'password',
        'device_name' => 'Postman',
    ]);

    $token = $response->json('token');

    $userResponse = $this->withToken($token)->getJson('api/users');
    $userResponse->assertOk();
    $userResponse->assertJsonFragment([
        'email' => $this->user->email,
    ]);
});

test('a user cannot login with inactive status', function () {
    $this->user->update(['status' => 'inactive']);

    $response = $this->postJson('api/login', [
        'email' => $this->user->email,
        'password' => 'password',
        'device_name' => 'Postman',
    ]);

    $response->assertForbidden();
    $response->assertJson([
        'message' => 'Your account is inactive. Please contact support.',
    ]);
    $this->assertDatabaseCount('personal_access_tokens', 0);
});

test('a user gets 401 error for wrong credentials with inactive status', function () {
    $this->user->update(['status' => 'inactive']);

    $response = $this->postJson('api/login', [
        'email' => $this->user->email,
        'password' => 'wrong-password',
        'device_name' => 'Postman',
    ]);

    $response->assertUnauthorized();
    $response->assertJson([
        'message' => 'The provided credentials are incorrect.',
    ]);
    $this->assertDatabaseCount('personal_access_tokens', 0);
});

test('a user gets 403 error for inactive status while authenticated', function () {
    $response = $this->postJson('api/login', [
        'email' => $this->user->email,
        'password' => 'password',
        'device_name' => 'Postman',
    ]);

    $token = $response->json('token');

    $this->user->update(['status' => 'inactive']);

    $userResponse = $this->withToken($token)->getJson('api/users');
    $userResponse->assertForbidden();
    $userResponse->assertJson([
        'message' => 'Your account is inactive. Please contact support.',
    ]);
});

test('a user requesting /user endpoint without token gets 401 error', function () {
    $userResponse = $this->getJson('api/users');

    $userResponse->assertUnauthorized();
    $userResponse->assertJson([
        'message' => 'Unauthenticated.',
    ]);
});

test('a user can login 5 attempts and gets 429 error on 6th attempt', function () {
    for($i = 0; $i < 5; $i++) {
        $response = $this->postJson('api/login', [
            'email' => $this->user->email,
            'password' => 'wrong-password',
            'device_name' => 'Postman',
        ]);

        $response->assertUnauthorized();
        $response->assertJson([
            'message' => 'The provided credentials are incorrect.',
        ]);
    }
    $response = $this->postJson('api/login', [
        'email' => $this->user->email,
        'password' => 'wrong-password',
        'device_name' => 'Postman',
    ]);

    $response->assertTooManyRequests();
    $response->assertJson([
        'message' => 'Too many login attempts. Please try again later.',
    ]);
    $response->assertHeader('Retry-After');
    $this->assertDatabaseCount('personal_access_tokens', 0);
});
