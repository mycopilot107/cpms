<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\ApiToken;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Token-based auth for the mobile app.
 *
 * Mirrors the web login's security posture: throttled by email+IP, rejects
 * disabled accounts even with a correct password, and never reveals whether an
 * email exists (same message for "no such user" and "wrong password").
 */
class AuthController extends Controller
{
    /**
     * POST /api/login — exchange credentials for a bearer token.
     */
    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
            'device_name' => ['sometimes', 'string', 'max:255'],
        ]);

        $email = strtolower(trim($credentials['email']));
        $throttleKey = Str::transliterate($email.'|'.$request->ip());

        $this->ensureIsNotRateLimited($throttleKey);

        // Email is globally unique (see users migration), so this resolves at
        // most one account across all tenants. The User model has no global
        // scope, so the lookup is safe pre-authentication.
        $user = User::where('email', $email)->first();

        if (! $user || ! is_string($user->password) || ! Hash::check($credentials['password'], $user->password) || ! $user->isActive()) {
            RateLimiter::hit($throttleKey);

            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        RateLimiter::clear($throttleKey);

        [$token, $plainToken] = ApiToken::issue(
            $user,
            $credentials['device_name'] ?? 'mobile',
        );

        $user->loadMissing('company');

        return response()->json([
            'token' => $plainToken,
            'expires_at' => $token->expires_at->toIso8601String(),
            'user' => new UserResource($user),
        ]);
    }

    /**
     * GET /api/me — the authenticated user.
     */
    public function me(Request $request): UserResource
    {
        $user = $request->user();
        $user->loadMissing('company');

        return new UserResource($user);
    }

    /**
     * POST /api/logout — revoke the token used for this request.
     */
    public function logout(Request $request): JsonResponse
    {
        $plain = $request->bearerToken();

        if ($plain) {
            ApiToken::where('token', ApiToken::hashToken($plain))
                ->where('user_id', $request->user()->id)
                ->update(['revoked_at' => now()]);
        }

        return response()->json(['message' => 'Logged out.']);
    }

    /**
     * @throws ValidationException
     */
    private function ensureIsNotRateLimited(string $throttleKey): void
    {
        if (! RateLimiter::tooManyAttempts($throttleKey, 5)) {
            return;
        }

        $seconds = RateLimiter::availableIn($throttleKey);

        throw ValidationException::withMessages([
            'email' => __('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }
}
