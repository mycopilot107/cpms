<?php

namespace App\Http\Middleware;

use App\Support\Currency;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

/**
 * Shares the active DISPLAY currency with every view, so marketing pages,
 * the landing-page token system and the subscription screens all render
 * prices in the visitor's chosen currency.
 *
 * Runs on the `web` group (after cookie decryption), so `cpms_currency` is
 * readable. The value is a plain cookie (exempted from encryption in
 * bootstrap/app.php) so the client-side auto-detect can set it too.
 *
 * This is presentation only — it never influences the amount charged, which
 * SubscriptionController always recomputes server-side in the charge currency.
 */
class ShareDisplayCurrency
{
    public function handle(Request $request, Closure $next): Response
    {
        View::share('currency', Currency::active($request));
        View::share('currencies', Currency::all());

        return $next($request);
    }
}
