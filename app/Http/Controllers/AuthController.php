<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Services\AuthService;

class AuthController extends Controller
{
    public function login(LoginRequest $request, AuthService $authService)
    {
        $validated = $request->validated();
        $response = $authService->login(
            $validated['email'],
            $validated['password'],
            $validated['device_name']
        );

        if (!$response) {
            return response()->json([
                'message' => 'The provided credentials are incorrect.',
            ], 401);
        }

        return response()->json([
            'token' => $response,
        ]);
    }
}
