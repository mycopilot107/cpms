<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Restrict the platform console (every company + subscription expiry) to
 * users whose database `is_admin` flag is enabled.
 *
 * The flag is checked from the authenticated User model and is not exposed to
 * normal user-management forms.
 */
class EnsureSuperAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if (! $user || ! $user->isSuperAdmin()) {
            abort(403, 'You are not authorised to access the platform console.');
        }

        return $next($request);
    }
}
