<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function signup(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:15'],
            'role' => ['required', 'in:user,admin,pharmacist'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'phone' => $validated['phone'],
        ]);

        // Automatically log in the user after registration and return token
        $token = $user->createToken('registration_token')->plainTextToken;

        return response()->json([
            'message' => 'User registered successfully',
            'user' => $user,
            'token' => $token
        ], 201);
    }

    public function login(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'username' => ['required', 'string'],
                'password' => ['required', 'string']
            ]);

            // Determine login field type
            $fieldType = filter_var($request->username, FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';

            $user = User::where($fieldType, $request->username)->first();

            if (!$user || !Hash::check($request->password, $user->password)) {
                Log::warning('Failed login attempt', ['username' => $request->username]);
                throw ValidationException::withMessages([
                    'username' => [trans('auth.failed')],
                ]);
            }

            // Revoke all existing tokens
            $user->tokens()->delete();

            $token = $user->createToken('auth_token')->plainTextToken;
            return response()->json([
                'token_type' => 'Bearer',
                'token' => $token,
                'user' => $user->only(['id', 'first_name', 'last_name', 'email', 'role'])
            ]);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422); // helpful debug info
        }
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Successfully logged out']);
    }

    public function user(Request $request): JsonResponse
    {
        return response()->json($request->user()->only([
            'id',
            'first_name',
            'last_name',
            'email',
            'phone',
            'role'
        ]));
    }
}
