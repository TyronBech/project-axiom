<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Department;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;

pest()->use(RefreshDatabase::class);

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

    $this->user->refresh();

    $this->user->assignRole('user.admin');
});

test('a user can logout', function () {
    $response = $this->postJson('api/login', [
        'email' => $this->user->email,
        'password' => 'password',
        'device_name' => 'Postman',
    ]);

    $response->assertOk();
    $token = $response->json('token');

    $logoutResponse = $this->withToken($token)->postJson('api/logout');
    $logoutResponse->assertNoContent();
    $this->assertDatabaseCount('personal_access_tokens', 0);

    auth()->forgetGuards();

    $userResponse = $this->withToken($token)->getJson('api/users');
    $userResponse->assertUnauthorized();
});

test('a user only logs out from the current device', function () {
    $postman = $this->postJson('api/login', [
        'email' => $this->user->email,
        'password' => 'password',
        'device_name' => 'Postman',
    ]);

    $postman->assertOk();
    $postmanToken = $postman->json('token');

    $mobile = $this->postJson('api/login', [
        'email' => $this->user->email,
        'password' => 'password',
        'device_name' => 'Mobile',
    ]);

    $mobile->assertOk();
    $mobileToken = $mobile->json('token');

    $logoutPostman = $this->withToken($postmanToken)->postJson('api/logout');
    $logoutPostman->assertNoContent();

    $this->assertDatabaseCount('personal_access_tokens', 1);

    auth()->forgetGuards();

    $userResponse = $this->withToken($postmanToken)->getJson('api/users');
    $userResponse->assertUnauthorized();

    $userResponseMobile = $this->withToken($mobileToken)->getJson('api/users');
    $userResponseMobile->assertOk();
});
