<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Gate for the login-free outreach page. With OUTREACH_ACCESS_KEY set, the
 * browser must have opened the page once with ?key=<value>; the session then
 * remembers it. Without a key the page only works outside production.
 */
class EnsureOutreachAccess
{
    private const SESSION_FLAG = 'outreach_access';

    public function handle(Request $request, Closure $next): Response
    {
        $key = (string) config('outreach.access_key');

        if ($key === '') {
            abort_if(app()->isProduction(), 404);

            return $next($request);
        }

        $given = $request->query('key');
        if (is_string($given) && hash_equals($key, $given)) {
            $request->session()->put(self::SESSION_FLAG, true);
        }

        abort_unless($request->session()->get(self::SESSION_FLAG) === true, 404);

        return $next($request);
    }
}
