<?php

namespace App\Http\Controllers;

use App\Support\Currency;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;

/**
 * Switches the visitor's DISPLAY currency (e.g. INR ↔ USD).
 *
 * Stores the choice in the `cpms_currency` cookie (a display preference, not a
 * destructive action, so a GET link is fine) and returns to the previous page.
 * Unknown/unsupported codes are ignored, so the URL can't force a bad state.
 * The cookie is exempt from encryption (bootstrap/app.php) so the client-side
 * auto-detect script can read/write the same value.
 */
class CurrencyController extends Controller
{
    public function switch(Request $request, string $code): RedirectResponse
    {
        $code = strtoupper($code);

        if (Currency::has($code)) {
            // Plain (unencrypted) cookie — see encryptCookies(except) in
            // bootstrap/app.php. raw=false, so it round-trips as a simple value.
            Cookie::queue(Cookie::make(
                Currency::COOKIE,
                $code,
                Currency::COOKIE_MINUTES,
                path: '/',
            ));
        }

        return redirect()->back(fallback: route('pricing'));
    }
}
