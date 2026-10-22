<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Route;
use Illuminate\View\View;

/**
 * Renders the keyword "Solutions" landing pages defined in config/landing.php.
 *
 * Each page is registered in routes/web.php with the name "landing.{key}"
 * (see the loop there). The controller derives {key} from the *current route
 * name* rather than a URL segment or a browser-supplied value — so the page
 * shown is pinned to the route that was matched, and there is nothing a user
 * can tamper with to render a different page. An unknown key 404s.
 *
 * These pages are public marketing content and hold no tenant data, so there
 * is no authorization concern here; the guardrail is simply that the key must
 * resolve to a defined config entry.
 */
class LandingController extends Controller
{
    public function show(): View
    {
        // "landing.contractors" -> "contractors". Uses the matched route name,
        // not request input, so the key cannot be spoofed from the browser.
        $name = (string) optional(Route::current())->getName();
        $key  = str_starts_with($name, 'landing.') ? substr($name, strlen('landing.')) : '';

        $page = config("landing.pages.$key");

        abort_unless(is_array($page), 404);

        return view('public.landing', [
            'key'  => $key,
            'page' => $page,
        ]);
    }
}
