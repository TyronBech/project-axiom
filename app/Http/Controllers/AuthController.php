<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Services\AuthService;
use App\Exceptions\InactiveAccountException;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function login(LoginRequest $request, AuthService $authService)
    {
        $validated = $request->validated();
        try {
            $response = $authService->login(
                $validated['email'],
                $validated['password'],
                $validated['device_name']
            );
        } catch (InactiveAccountException $e) {
            return response()->json([
                'message' => 'Your account is inactive. Please contact support.',
            ], 403);
        }

        if (!$response) {
            return response()->json([
                'message' => 'The provided credentials are incorrect.',
            ], 401);
        }

        return response()->json([
            'token' => $response,
        ]);
    }

    public function logout(Request $request) {
        $request->user()->currentAccessToken()->delete();

        return response()->noContent();
    }
}
