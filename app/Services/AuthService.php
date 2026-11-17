<?php

namespace App\Services;

use App\Enums\UserStatus;
use App\Exceptions\InactiveAccountException;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    public function login(string $email, string $password, string $deviceName): ?string
    {
        $user = User::where('email', $email)->first();

        if (!$user || !Hash::check($password, $user->password)) {
            return null;
        }

        if (!$user->status || $user->status !== UserStatus::ACTIVE) {
            throw new InactiveAccountException();
        }

        return $user->createToken($deviceName)->plainTextToken;
    }
}
