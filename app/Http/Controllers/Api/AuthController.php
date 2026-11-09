<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);
        $user = User::where('email', $credentials['email'])->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            return response()->json([
                'type' => 'about:blank', 'title' => 'Unauthorized', 'status' => 401,
                'detail' => 'Email atau password tidak valid.',
                'instance' => $request->getRequestUri(),
            ], 401, ['Content-Type' => 'application/problem+json']);
        }

        return response()->json([
            'data' => ['user' => $user, 'token' => $user->createToken('api')->plainTextToken],
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json(['data' => $request->user()]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()?->delete();

        return response()->json(['message' => 'Token berhasil dicabut.']);
    }
}
