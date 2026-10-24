<?php

namespace App\Mail;

use App\Models\ContactMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Queue\SerializesModels;

/**
 * Notification sent to the Best CPMS inbox when a visitor submits the public
 * Contact or Feedback form. Recipient is configurable via config('mail.contact_to')
 * (defaults to shobha.solanki107@gmail.com). Reply-To is set to the visitor so a reply goes
 * straight back to them.
 */
class ContactMessageReceived extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public ContactMessage $submission)
    {
    }

    public function envelope(): Envelope
    {
        $isFeedback = $this->submission->isFeedback();

        return new Envelope(
            subject: ($isFeedback ? 'New feedback' : 'New contact enquiry')
                . ': ' . ($this->submission->subject ?: $this->submission->name),
            replyTo: [new Address($this->submission->email, $this->submission->name)],
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.contact-message');
    }
}
