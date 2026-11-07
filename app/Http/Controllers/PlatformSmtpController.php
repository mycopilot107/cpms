<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Mail\Message;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

/** Super-admin SMTP check: show the active mail settings and send a test. */
class PlatformSmtpController extends Controller
{
    public function index(Request $request): View
    {
        $port = (int) config('mail.mailers.smtp.port');
        $scheme = config('mail.mailers.smtp.scheme');

        return view('platform.smtp-test', [
            'settings' => [
                'MAIL_MAILER' => config('mail.default'),
                'MAIL_HOST' => config('mail.mailers.smtp.host'),
                'MAIL_PORT' => $port ?: null,
                'MAIL_SCHEME' => $scheme ?: ($port === 465 ? '(none — SSL is used automatically on port 465)' : '(none — STARTTLS if offered)'),
                'MAIL_USERNAME' => config('mail.mailers.smtp.username'),
                'MAIL_PASSWORD' => config('mail.mailers.smtp.password') ? 'set (hidden)' : null,
                'MAIL_FROM_ADDRESS' => config('mail.from.address'),
                'MAIL_FROM_NAME' => config('mail.from.name'),
                'CONTACT_TO' => config('mail.contact_to'),
            ],
            'defaultTo' => $request->user()->email,
        ]);
    }

    public function send(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'to_email' => ['required', 'email', 'max:255'],
        ]);

        $to = $validated['to_email'];
        $started = microtime(true);

        try {
            Mail::raw(
                "This is a test email from the Best CPMS platform console.\n\n"
                .'Sent at: '.now()->toDateTimeString()."\n"
                .'SMTP host: '.config('mail.mailers.smtp.host').':'.config('mail.mailers.smtp.port')."\n"
                .'From: '.config('mail.from.address'),
                function (Message $message) use ($to): void {
                    $message->to($to)->subject('SMTP test — Best CPMS');
                },
            );

            $result = [
                'success' => true,
                'message' => "Email sent to {$to}",
                'detail' => 'The SMTP server accepted it in '.round(microtime(true) - $started, 1).'s. Check the inbox and the spam folder.',
            ];
        } catch (\Throwable $e) {
            $result = [
                'success' => false,
                'message' => 'SMTP failed',
                'detail' => $e->getMessage(),
            ];
        }

        return back()->with('smtp_result', $result)->withInput();
    }
}
