<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreContactRequest;
use App\Http\Requests\StoreFeedbackRequest;
use App\Mail\ContactMessageReceived;
use App\Models\ContactMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    /** Persist a submission and email the Best CPMS inbox. Mail failure never
     *  breaks the request — the row is saved first, then we try to notify. */
    protected function record(array $data, string $type): ContactMessage
    {
        $message = ContactMessage::create($data + [
            'type' => $type,
            'ip_address' => request()->ip(),
        ]);

        try {
            Mail::to(config('mail.contact_to', 'shobha.solanki107@gmail.com'))
                ->send(new ContactMessageReceived($message));
        } catch (\Throwable $e) {
            // Saved to the database regardless; surface the mail issue in logs.
            Log::warning('Contact notification email failed: ' . $e->getMessage());
        }

        return $message;
    }

    public function contact(): \Illuminate\View\View
    {
        return view('public.contact');
    }

    public function storeContact(StoreContactRequest $request): RedirectResponse
    {
        $this->record(
            $request->safe()->only(['name', 'email', 'phone', 'company', 'subject', 'message']),
            ContactMessage::TYPE_CONTACT,
        );

        return redirect()
            ->route('contact')
            ->with('status', "Thanks for reaching out — we've got your message and will reply within one business day.");
    }

    public function feedback(): \Illuminate\View\View
    {
        return view('public.feedback');
    }

    public function storeFeedback(StoreFeedbackRequest $request): RedirectResponse
    {
        $this->record(
            $request->safe()->only(['name', 'email', 'company', 'rating', 'message']),
            ContactMessage::TYPE_FEEDBACK,
        );

        return redirect()
            ->route('feedback')
            ->with('status', 'Thank you for the feedback — it genuinely helps us build Best CPMS better.');
    }
}
