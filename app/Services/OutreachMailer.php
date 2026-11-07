<?php

namespace App\Services;

use App\Models\SendEmail;
use Illuminate\Mail\Message;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;

/**
 * Sends queued outreach emails. Each address has a single row, so once a row
 * is marked sent (or unsubscribed) that address is never emailed again.
 */
class OutreachMailer
{
    /**
     * @return array{sent: int, failed: int}
     */
    public function sendPending(int $limit): array
    {
        $sent = 0;
        $failed = 0;

        $rows = SendEmail::query()->pending()->orderBy('id')->limit(max(1, $limit))->get();

        foreach ($rows as $row) {
            $unsubscribeUrl = URL::signedRoute('email.unsubscribe', ['sendEmail' => $row->id]);
            $html = $row->body.$this->footer($unsubscribeUrl);

            try {
                Mail::html($html, function (Message $message) use ($row, $unsubscribeUrl): void {
                    $message->to($row->email)->subject($row->subject);
                    $message->getSymfonyMessage()->getHeaders()
                        ->addTextHeader('List-Unsubscribe', '<'.$unsubscribeUrl.'>');
                });

                $row->forceFill(['is_send' => true, 'sent_at' => now(), 'error' => null])->save();
                $sent++;
            } catch (\Throwable $e) {
                $row->forceFill(['error' => mb_substr($e->getMessage(), 0, 1000)])->save();
                $failed++;
            }
        }

        return ['sent' => $sent, 'failed' => $failed];
    }

    private function footer(string $unsubscribeUrl): string
    {
        return '<p style="margin-top:24px;font-size:12px;color:#64748b;">'
            .'You received this email because your address is listed publicly on your business website. '
            .'<a href="'.e($unsubscribeUrl).'" style="color:#64748b;">Unsubscribe</a> and we will not email you again.'
            .'</p>';
    }
}
