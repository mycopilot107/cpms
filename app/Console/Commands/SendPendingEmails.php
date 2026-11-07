<?php

namespace App\Console\Commands;

use App\Services\OutreachMailer;
use Illuminate\Console\Command;

class SendPendingEmails extends Command
{
    protected $signature = 'emails:send-pending {--limit= : Max emails to send in this run}';

    protected $description = 'Send queued outreach emails (send_email rows) that have not been sent yet';

    public function handle(OutreachMailer $mailer): int
    {
        $limit = (int) ($this->option('limit') ?: config('outreach.batch_size', 20));

        $result = $mailer->sendPending($limit);

        $this->info("Sent {$result['sent']}, failed {$result['failed']}.");

        return self::SUCCESS;
    }
}
