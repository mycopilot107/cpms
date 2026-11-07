<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateAccountPasswordRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

/** Any signed-in user, including the platform admin, changing their own password. */
class AccountPasswordController extends Controller
{
    public function edit(Request $request): View
    {
        return view('account.password', [
            'hasPassword' => is_string($request->user()->password),
        ]);
    }

    public function update(UpdateAccountPasswordRequest $request): RedirectResponse
    {
        $user = $request->user();

        // The model hashes the password and revokes the user's app tokens on save.
        $user->password = $request->validated('password');
        $user->setRememberToken(Str::random(60));
        $user->save();

        $request->session()->regenerate();

        return redirect()
            ->route('account.password')
            ->with('status', 'Your password has been changed. Sign in with the new password next time, including on the mobile app.');
    }
}
