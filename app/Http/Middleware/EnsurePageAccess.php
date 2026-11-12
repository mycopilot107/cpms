<?php

namespace App\Http\Middleware;

use App\Support\AppPages;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Blocks a signed-in user from opening a page their role does not include.
 * Routes that are not in the page list (account, logout, the platform
 * console) are left to their own checks.
 */
class EnsurePageAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $page = AppPages::pageForRoute($request->route()?->getName());

        if ($user !== null && $page !== null && ! $user->canAccess($page)) {
            abort(403, 'Your role does not include this page.');
        }

        if ($user !== null && str_starts_with((string) $request->route()?->getName(), 'roles.') && ! $user->isAdmin()) {
            abort(403, 'Only an owner or admin can manage roles.');
        }

        return $next($request);
    }
}
