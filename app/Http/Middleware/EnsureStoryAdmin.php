<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Restrict the success-story moderation screens to the single site
 * administrator identified by config('stories.admin_email').
 *
 * The app has no platform-level "super admin" role (every User role is
 * company-scoped), so moderation is gated by matching the signed-in user's
 * email against the configured one. A blank config locks the screen to
 * everyone — the safe default in an unconfigured environment.
 */
class EnsureStoryAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $admin = trim((string) config('stories.admin_email'));
        $user  = Auth::user();

        if ($admin === '' || ! $user || strcasecmp(trim((string) $user->email), $admin) !== 0) {
            abort(403, 'You are not authorised to moderate success stories.');
        }

        return $next($request);
    }
}
