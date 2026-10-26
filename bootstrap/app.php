<?php

use App\Http\Middleware\EnsureAccountActive;
use App\Http\Middleware\EnsureStoryAdmin;
use App\Http\Middleware\EnsureSubscriptionActive;
use App\Http\Middleware\EnsureSuperAdmin;
use App\Http\Middleware\ShareDisplayCurrency;
use App\Support\Currency;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // A local tunnel or reverse proxy terminates HTTPS and forwards from
        // this machine. Trusting only loopback keeps forwarded headers from
        // the open internet from being believed.
        $middleware->trustProxies(at: ['127.0.0.1', '::1']);

        $middleware->alias([
            'subscription' => EnsureSubscriptionActive::class,
            'story-admin' => EnsureStoryAdmin::class,
            'super-admin' => EnsureSuperAdmin::class,
        ]);

        // Share the active display currency (INR/USD) with every web view, so
        // prices render in the visitor's currency sitewide.
        $middleware->web(append: [
            EnsureAccountActive::class,
            ShareDisplayCurrency::class,
        ]);

        // The display-currency cookie is a non-sensitive UI preference and is
        // also written client-side by the auto-detect script, so it must stay
        // unencrypted to round-trip between JS and the server.
        $middleware->encryptCookies(except: [
            Currency::COOKIE,
        ]);

        // Send unauthenticated web users to the login page (this framework
        // version returns a bare 401 otherwise).
        $middleware->redirectGuestsTo(fn () => route('login'));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
