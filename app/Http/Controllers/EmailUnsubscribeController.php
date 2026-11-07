<?php

namespace App\Http\Controllers;

use App\Models\SendEmail;
use Illuminate\View\View;

/** Signed one-click unsubscribe link from an outreach email. */
class EmailUnsubscribeController extends Controller
{
    public function __invoke(SendEmail $sendEmail): View
    {
        if ($sendEmail->unsubscribed_at === null) {
            $sendEmail->forceFill(['unsubscribed_at' => now()])->save();
        }

        return view('public.unsubscribed', ['email' => $sendEmail->email]);
    }
}
