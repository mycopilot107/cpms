<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\PasswordResetLinkRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;

class PasswordResetLinkController extends Controller
{
    /** Show the "enter your email" form. */
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * Email a password reset link.
     *
     * SECURITY: we deliberately respond with the same neutral confirmation
     * regardless of whether the email belongs to a real account, so this
     * endpoint can't be used to discover which addresses are registered.
     * The actual sending (and the broker's own per-user throttle) still runs;
     * only the message shown to the visitor is constant. The route is also
     * rate-limited to blunt abuse.
     */
    public function store(PasswordResetLinkRequest $request): RedirectResponse
    {
        Password::sendResetLink($request->only('email'));

        return back()->with('status', __('passwords.sent_generic'));
    }
}
