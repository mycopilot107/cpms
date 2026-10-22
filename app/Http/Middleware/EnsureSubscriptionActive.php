<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Gate the application behind an active trial or paid subscription.
 *
 * The spec is explicit: when a trial expires we must NOT delete data. The
 * company can still log in, but is routed to an "upgrade" screen until a
 * subscription is active again. This middleware enforces exactly that.
 */
class EnsureSubscriptionActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if ($user) {
            $company = $user->company;

            // hasAccess() = on trial OR has a paid subscription that is active
            // and not past its end date.
            if (! $company || ! $company->hasAccess()) {
                return redirect()->route('subscription.expired');
            }
        }

        return $next($request);
    }
}
