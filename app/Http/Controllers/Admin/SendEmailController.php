<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SendEmail;
use App\Models\User;
use App\Services\OutreachMailer;
use App\Services\PublicBusinessEmailDiscoveryService;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Login-free outreach page: find public contractor/builder emails by search
 * text, queue them, and send the Best CPMS introduction once per address.
 * Access is gated by EnsureOutreachAccess.
 */
class SendEmailController extends Controller
{
    private const DEFAULT_SUBJECT = 'Run every construction site from one place — Best CPMS (15-day free trial)';

    private const DEFAULT_BODY = <<<'HTML'
<p>Dear Sir/Madam,</p>

<p>I hope you're doing well.</p>

<p>Running several sites usually means the programme in one spreadsheet, the BOQ in another, and site updates arriving on WhatsApp. <strong>Best CPMS</strong> puts all of it in one place for contractors and builders.</p>

<p>With Best CPMS you can:</p>
<ul>
<li>Plan projects with milestones, tasks, a calendar, and a Gantt chart</li>
<li>Build your BOQ and track budget, expenses, and client payments</li>
<li>Let site engineers and workers update progress from an Android app</li>
<li>Export project, cost, and progress reports in one click</li>
<li>Give each person a role, so site staff see tasks and the office sees cost</li>
</ul>

<h3>Start a 15-day free trial — no card needed</h3>

<p>Set up your first project in an afternoon:<br>
<a href="https://bestcpms.com">https://bestcpms.com</a></p>

<p>If you'd like a quick walkthrough for your team, just reply to this email.</p>

<p>Best regards,<br>
Team Best CPMS<br>
<a href="https://bestcpms.com">https://bestcpms.com</a></p>
HTML;

    public function __construct(
        private readonly PublicBusinessEmailDiscoveryService $emailDiscovery,
    ) {}

    public function index(Request $request): View
    {
        $status = $request->query('status');

        $emails = SendEmail::query()
            ->when($status === 'sent', fn ($q) => $q->where('is_send', true))
            ->when($status === 'pending', fn ($q) => $q->pending())
            ->when($status === 'failed', fn ($q) => $q->where('is_send', false)->whereNotNull('error'))
            ->when($status === 'unsubscribed', fn ($q) => $q->whereNotNull('unsubscribed_at'))
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return view('admin.send-email.index', [
            'emails' => $emails,
            'status' => $status,
            'counts' => [
                'all' => SendEmail::query()->count(),
                'sent' => SendEmail::query()->where('is_send', true)->count(),
                'pending' => SendEmail::query()->pending()->count(),
                'failed' => SendEmail::query()->where('is_send', false)->whereNotNull('error')->count(),
                'unsubscribed' => SendEmail::query()->whereNotNull('unsubscribed_at')->count(),
            ],
            'defaultSubject' => self::DEFAULT_SUBJECT,
            'defaultBody' => self::DEFAULT_BODY,
            'emailLimit' => PublicBusinessEmailDiscoveryService::EMAILS_PER_REQUEST,
            'batchSize' => (int) config('outreach.batch_size', 20),
        ]);
    }

    /** Queue a pasted, comma-separated list of addresses. */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'emails' => ['required', 'string', 'max:20000'],
            'subject' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string', 'max:20000'],
        ]);

        $addresses = collect(preg_split('/[\s,;]+/', $validated['emails']) ?: [])
            ->map(fn (string $email) => SendEmail::normalize($email))
            ->filter(fn (string $email) => $email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL))
            ->unique()
            ->values()
            ->all();

        if ($addresses === []) {
            return back()->withInput()->withErrors(['emails' => 'No valid email addresses found.']);
        }

        $result = $this->queue($addresses, $validated['subject'], $validated['body'], null);

        return redirect()->route('admin.send-email.index')
            ->with('status', $this->statusMessage($result))
            ->with('queued_emails', $result['queued']);
    }

    /** Find public business emails for the search text and queue the new ones. */
    public function search(Request $request): RedirectResponse
    {
        if (function_exists('set_time_limit')) {
            @set_time_limit(300);
        }

        $validated = $request->validate([
            'query' => ['required', 'string', 'min:3', 'max:200'],
            'subject' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string', 'max:20000'],
        ]);

        $found = $this->emailDiscovery->discover(
            $validated['query'],
            PublicBusinessEmailDiscoveryService::EMAILS_PER_REQUEST,
        );

        if ($found === []) {
            return back()->withInput()->withErrors([
                'query' => 'No public contact emails were found for that search. Try another city or wording.',
            ]);
        }

        $result = $this->queue($found, $validated['subject'], $validated['body'], $validated['query']);

        return redirect()->route('admin.send-email.index')
            ->with('status', $this->statusMessage($result))
            ->with('queued_emails', $result['queued']);
    }

    /** Send one batch right away, without waiting for the scheduler. */
    public function sendNow(OutreachMailer $mailer): RedirectResponse
    {
        $result = $mailer->sendPending((int) config('outreach.batch_size', 20));

        return redirect()->route('admin.send-email.index', ['status' => 'sent'])
            ->with('status', "Sent {$result['sent']} email(s)".($result['failed'] ? ", {$result['failed']} failed" : '').'.');
    }

    /**
     * @param  list<string>  $addresses
     * @return array{queued: list<string>, duplicates: list<string>, customers: list<string>}
     */
    private function queue(array $addresses, string $subject, string $body, ?string $searchQuery): array
    {
        $addresses = array_values(array_unique(array_map(SendEmail::normalize(...), $addresses)));

        $alreadyListed = SendEmail::query()->whereIn('email', $addresses)->pluck('email')->flip();
        $customers = User::query()->whereIn('email', $addresses)->pluck('email')->flip();

        $result = ['queued' => [], 'duplicates' => [], 'customers' => []];

        foreach ($addresses as $address) {
            if ($alreadyListed->has($address)) {
                $result['duplicates'][] = $address;

                continue;
            }
            if ($customers->has($address)) {
                $result['customers'][] = $address;

                continue;
            }

            try {
                SendEmail::create([
                    'email' => $address,
                    'subject' => $subject,
                    'body' => $body,
                    'search_query' => $searchQuery,
                    'is_send' => false,
                ]);
                $result['queued'][] = $address;
            } catch (UniqueConstraintViolationException) {
                $result['duplicates'][] = $address;
            }
        }

        return $result;
    }

    /**
     * @param  array{queued: list<string>, duplicates: list<string>, customers: list<string>}  $result
     */
    private function statusMessage(array $result): string
    {
        $parts = [count($result['queued']).' new email(s) queued.'];

        if ($result['duplicates'] !== []) {
            $parts[] = count($result['duplicates']).' skipped — already emailed or queued before.';
        }
        if ($result['customers'] !== []) {
            $parts[] = count($result['customers']).' skipped — already Best CPMS users.';
        }

        return implode(' ', $parts);
    }
}
