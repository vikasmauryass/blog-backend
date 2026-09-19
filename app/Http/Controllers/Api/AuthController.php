<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],

            'email' => [
                'required',
                'email',
                'max:255',
                'unique:users,email',
            ],

            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
            ],
        ]);

        $user = User::create([
            'role_id' => 2,
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'status' => true,
        ]);

        $token = $user->createToken('blog-api')->accessToken;

        return response()->json([
            'message' => 'Registration successful',
            'user' => $user,
            'token' => $token,
        ], 201);
    }

    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (!$user || !Hash::check(
            $validated['password'],
            $user->password
        )) {
            return response()->json([
                'message' => 'Invalid credentials',
            ], 401);
        }

        if (!$user->status) {
            return response()->json([
                'message' => 'Account is inactive',
            ], 403);
        }

        $token = $user->createToken('blog-api')->accessToken;

        return response()->json([
            'message' => 'Login successful',
            'user' => $user->load('role'),
            'token' => $token,
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()
            ->token()
            ->revoke();

        return response()->json([
            'message' => 'Logout successful',
        ]);
    }

    public function me(Request $request)
    {
        return response()->json([
            'user' => $request->user()->load('role'),
        ]);
    }
}
