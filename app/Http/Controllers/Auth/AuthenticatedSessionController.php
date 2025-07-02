<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class AuthenticatedSessionController extends Controller
{
    /**
     * Handle an incoming authentication request.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'username'=>['required','string'],
            'password'=>['required','string']
        ]);
        $login_type = filter_var($request->username, FILTER_VALIDATE_EMAIL) ? 'email' : 'phone_number';

        $credentials = [
        $login_type => $request->username,
        'password' => $request->password,
        ];
       if (Auth::attempt($credentials)) {
        return response()->json([
            'message' => 'Login successful',
            'user' => Auth::user()
        ]);
       }
        $request->authenticate();

        $request->session()->regenerate();

    return response()->json([
        'message' => 'Invalid credentials'
    ], 401);

    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): Response
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return response()->noContent();
    }
}
