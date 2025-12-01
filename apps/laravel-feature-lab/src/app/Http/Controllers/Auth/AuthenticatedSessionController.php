<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class AuthenticatedSessionController extends Controller
{
    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): JsonResponse|Response
    {
        $request->authenticate();

        // For API, create and return a Sanctum token
        if ($request->wantsJson() || $request->is('api/*')) {
            $token = $request->user()->createToken('auth-token')->plainTextToken;
            
            return response()->json([
                'user' => $request->user(),
                'token' => $token,
            ], 201);
        }

        // For web, use session
        $request->session()->regenerate();
        return response()->noContent();
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): Response
    {
        // For API, revoke the current token
        if ($request->wantsJson() || $request->is('api/*')) {
            $user = $request->user();
            if ($user) {
                // Delete the current access token
                $token = $user->currentAccessToken();
                if ($token) {
                    $token->delete();
                } else {
                    // If no current token (e.g., in tests), delete all tokens
                    $user->tokens()->delete();
                }
            }
            return response()->noContent();
        }

        // For web, use session
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->noContent();
    }
}
