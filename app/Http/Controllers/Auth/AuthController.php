<?php

namespace App\Http\Controllers\Auth;

use App\Actions\Auth\LoginUserAction;
use App\Actions\Auth\LogoutUserAction;
use App\Actions\Auth\RegisterUserAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginUserRequest;
use App\Http\Requests\Auth\RegisterUserRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function register(RegisterUserRequest $request, RegisterUserAction $action): JsonResponse
    {
        $token = $action->execute($request->validated(), $request->attributes->get('guest_token'));

        return response()->json([
            'message' => 'User created successfully',
            'token' => $token,
        ], 201);
    }

    public function login(LoginUserRequest $request, LoginUserAction $action): JsonResponse
    {
        $token = $action->execute($request->validated(), $request->attributes->get('guest_token'));

        return response()->json([
            'message' => 'User logged in successfully',
            'token' => $token,
        ]);
    }

    public function logout(Request $request, LogoutUserAction $action): JsonResponse
    {
        $action->execute($request->user('sanctum'));

        return response()->json(status: 204);
    }
}
