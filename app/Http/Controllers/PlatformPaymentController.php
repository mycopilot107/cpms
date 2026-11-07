<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/** Every Razorpay checkout across all companies, for the platform console. */
class PlatformPaymentController extends Controller
{
    private const STATUSES = [Payment::STATUS_PAID, Payment::STATUS_CREATED, Payment::STATUS_FAILED];

    public function index(Request $request): View|StreamedResponse
    {
        $filters = $this->filters($request);
        $query = $this->filteredQuery($filters);

        if ($request->query('export') === 'csv') {
            return $this->exportCsv($query);
        }

        return view('platform.payments.index', [
            'payments' => (clone $query)->with(['company', 'user'])->latest()->paginate(25)->withQueryString(),
            'totals' => (clone $query)
                ->where('status', Payment::STATUS_PAID)
                ->selectRaw('currency, SUM(amount) as total, COUNT(*) as payments')
                ->groupBy('currency')
                ->get(),
            'counts' => collect(self::STATUSES)->mapWithKeys(fn (string $status) => [
                $status => Payment::acrossCompanies()->where('status', $status)->count(),
            ]),
            'filters' => $filters,
            'statuses' => self::STATUSES,
        ]);
    }

    public function show(int $payment): View
    {
        $record = Payment::acrossCompanies()
            ->with(['company', 'user', 'subscription', 'plan'])
            ->findOrFail($payment);

        return view('platform.payments.show', ['payment' => $record]);
    }

    /**
     * @return array{q: string, status: string, from: ?string, to: ?string}
     */
    private function filters(Request $request): array
    {
        $status = (string) $request->query('status', 'all');
        $date = fn (string $key): ?string => preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $request->query($key)) ? (string) $request->query($key) : null;

        return [
            'q' => trim((string) $request->query('q', '')),
            'status' => in_array($status, self::STATUSES, true) ? $status : 'all',
            'from' => $date('from'),
            'to' => $date('to'),
        ];
    }

    /**
     * @param  array{q: string, status: string, from: ?string, to: ?string}  $filters
     * @return Builder<Payment>
     */
    private function filteredQuery(array $filters): Builder
    {
        return Payment::acrossCompanies()
            ->when($filters['status'] !== 'all', fn ($q) => $q->where('status', $filters['status']))
            ->when($filters['from'], fn ($q, $from) => $q->whereDate('created_at', '>=', $from))
            ->when($filters['to'], fn ($q, $to) => $q->whereDate('created_at', '<=', $to))
            ->when($filters['q'] !== '', function ($q) use ($filters): void {
                $term = '%'.$filters['q'].'%';
                $q->where(function ($inner) use ($term): void {
                    $inner->where('receipt', 'like', $term)
                        ->orWhere('razorpay_order_id', 'like', $term)
                        ->orWhere('razorpay_payment_id', 'like', $term)
                        ->orWhereHas('company', fn ($c) => $c->where('name', 'like', $term)->orWhere('email', 'like', $term));
                });
            });
    }

    /**
     * @param  Builder<Payment>  $query
     */
    private function exportCsv(Builder $query): StreamedResponse
    {
        $rows = $query->with(['company', 'user'])->latest()->lazy(500);

        return response()->streamDownload(function () use ($rows): void {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Date', 'Company', 'Paid by', 'Status', 'Seats', 'Months', 'Price per user', 'Amount', 'Currency', 'Receipt', 'Razorpay order', 'Razorpay payment', 'Failure reason']);

            foreach ($rows as $payment) {
                fputcsv($out, array_map($this->csvSafe(...), [
                    $payment->created_at?->format('Y-m-d H:i'),
                    $payment->company?->name,
                    $payment->user?->email,
                    $payment->status,
                    $payment->seats,
                    $payment->months,
                    $payment->price_per_user,
                    $payment->amount,
                    $payment->currency,
                    $payment->receipt,
                    $payment->razorpay_order_id,
                    $payment->razorpay_payment_id,
                    $payment->failure_reason,
                ]));
            }

            fclose($out);
        }, 'payments-'.now()->format('Y-m-d').'.csv', ['Content-Type' => 'text/csv']);
    }

    /** Stop a spreadsheet from running text that starts like a formula. */
    private function csvSafe(mixed $value): mixed
    {
        return is_string($value) && preg_match('/^[=+\-@\t\r]/', $value) ? "'".$value : $value;
    }
}
