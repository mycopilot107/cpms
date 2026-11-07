<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateAccountProfileRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** Any signed-in user's own name, email, and phone, whatever their role. */
class AccountProfileController extends Controller
{
    public function edit(Request $request): View
    {
        return view('account.profile', [
            'user' => $request->user()->loadMissing(['company', 'role']),
        ]);
    }

    public function update(UpdateAccountProfileRequest $request): RedirectResponse
    {
        $request->user()->update($request->safe()->only(['name', 'email', 'phone']));

        return redirect()
            ->route('account.profile')
            ->with('status', 'Your profile has been saved.');
    }
}
