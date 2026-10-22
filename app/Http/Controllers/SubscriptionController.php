<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Plan;
use App\Services\RazorpayService;
use App\Services\SubscriptionActivator;
use App\Support\Currency;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use RuntimeException;

/**
 * Paid-subscription checkout via Razorpay.
 *
 * These actions live OUTSIDE the `subscription` gate (see routes/web.php) so an
 * expired company can still reach them — otherwise the gate would bounce them
 * to the expired page in a loop. Only owners/admins may purchase.
 *
 * The price is ALWAYS recomputed on the server from the active plan's per-user
 * price (DB-sourced via App\Support\Currency, with config/billing.php as the
 * fallback) and the validated seat/term selection. The amount posted by the
 * browser is ignored.
 */
class SubscriptionController extends Controller
{
    public function __construct(
        private readonly RazorpayService $razorpay,
        private readonly SubscriptionActivator $activator,
    ) {}

    /** Plan selection screen. */
    public function plans(Request $request): View|RedirectResponse
    {
        $this->authorizeBilling($request);

        $company = $request->user()->company;

        // Display-currency reference (INR/USD). Payment is still CHARGED in the
        // charge currency (INR) — these only drive the on-screen reference and
        // the "billed in …" note, never the amount.
        $display = Currency::active($request);
        $charge = Currency::charge();

        return view('subscription.plans', [
            'company' => $company,
            'seatOptions' => config('billing.seat_options'),
            'termOptions' => config('billing.term_options'),
            'billingPeriod' => Currency::billingPeriod(),
            'pricePerUser' => Currency::pricePerUser($charge),
            'minSeats' => (int) config('billing.min_seats'),
            'currencySymbol' => config('billing.currency_symbol'),
            'usedSeats' => $company->usedSeats(),
            'activeSubscription' => $company->activeSubscription(),
            'gatewayReady' => $this->razorpay->configured(),
            'displayCurrency' => $display,
            'chargeCurrency' => $charge,
            'displayPricePerUser' => Currency::pricePerUser($display),
            'displayIsCharge' => ($display['code'] ?? null) === ($charge['code'] ?? null),
            'payments' => $company->payments()->latest()->limit(8)->get(),
        ]);
    }

    /** Validate the selection, create a Razorpay order, and show Checkout. */
    public function checkout(Request $request): View|RedirectResponse
    {
        $this->authorizeBilling($request);

        $company = $request->user()->company;
        $minSeats = (int) config('billing.min_seats');

        // Charge price is DB-first (editable plan) with config as fallback, and
        // is always taken from the CHARGE currency — never the display currency.
        $charge = Currency::charge();
        $currency = (string) ($charge['code'] ?? config('billing.currency', 'INR'));
        $pricePerUser = Currency::pricePerUser($charge);

        // The plan + period this sale is recorded under (historical accuracy if
        // the price is edited later).
        $plan = Plan::current();
        $planId = $plan?->id;
        $billingPeriod = Currency::billingPeriod();

        $usedSeats = $company->usedSeats();

        // Seats must be an allowed option, at/above the minimum, and never
        // fewer than the company's current headcount. Term is counted in months.
        $validated = $request->validate([
            'seats' => ['required', 'integer', Rule::in(config('billing.seat_options'))],
            'months' => ['required', 'integer', Rule::in(config('billing.term_options'))],
        ]);

        $seats = (int) $validated['seats'];
        $months = (int) $validated['months'];

        if ($seats < $minSeats) {
            throw ValidationException::withMessages([
                'seats' => "You must license at least {$minSeats} users.",
            ]);
        }

        if ($seats < $usedSeats) {
            throw ValidationException::withMessages([
                'seats' => "Your company already has {$usedSeats} users. Choose at least that many seats.",
            ]);
        }

        if (! $this->razorpay->configured()) {
            return redirect()
                ->route('subscription.plans')
                ->with('error', 'Payments are not configured yet. Please try again later.');
        }

        // Server-computed total — the browser's number is never trusted.
        $amount = $seats * $months * $pricePerUser;

        $payment = new Payment([
            'user_id' => $request->user()->id,
            'plan_id' => $planId,
            'seats' => $seats,
            'months' => $months,
            'billing_period' => $billingPeriod,
            'price_per_user' => $pricePerUser,
            'amount' => $amount,
            'currency' => $currency,
            'status' => Payment::STATUS_CREATED,
            'receipt' => 'rcpt_'.$company->id.'_'.Str::lower(Str::random(12)),
        ]);
        // company_id is stamped by the BelongsToCompany trait.
        $payment->save();

        try {
            $order = $this->razorpay->createOrder(
                amountInPaise: $payment->amountInPaise(),
                currency: $currency,
                receipt: $payment->receipt,
                notes: [
                    'company_id' => (string) $company->id,
                    'company' => (string) $company->name,
                    'seats' => (string) $seats,
                    'months' => (string) $months,
                ],
            );
        } catch (RuntimeException $e) {
            $payment->update([
                'status' => Payment::STATUS_FAILED,
                'failure_reason' => $e->getMessage(),
            ]);

            return redirect()
                ->route('subscription.plans')
                ->with('error', 'Could not start the payment: '.$e->getMessage());
        }

        $payment->update(['razorpay_order_id' => $order['id']]);

        // Display-currency reference only. The amount CHARGED is $payment->amount
        // (INR) / $order['amount'] (paise) — computed above and never derived
        // from this. These feed the on-screen reference and "billed in …" note.
        $display = Currency::active($request);
        $charge = Currency::charge();
        $displayPricePerUser = Currency::pricePerUser($display);

        return view('subscription.checkout', [
            'payment' => $payment,
            'order' => $order,
            'company' => $company,
            'user' => $request->user(),
            'razorpayKey' => $this->razorpay->keyId(),
            'currencySymbol' => config('billing.currency_symbol'),
            'billingPeriod' => $billingPeriod,
            'displayCurrency' => $display,
            'chargeCurrency' => $charge,
            'displayPricePerUser' => $displayPricePerUser,
            'displayAmount' => $seats * $months * $displayPricePerUser,
            'displayIsCharge' => ($display['code'] ?? null) === ($charge['code'] ?? null),
        ]);
    }

    /** Callback from Razorpay Checkout — verify the signature and activate. */
    public function verify(Request $request): RedirectResponse
    {
        $this->authorizeBilling($request);

        $data = $request->validate([
            'razorpay_order_id' => ['required', 'string'],
            'razorpay_payment_id' => ['required', 'string'],
            'razorpay_signature' => ['required', 'string'],
        ]);

        // Scoped by the tenant global scope — an order id from another
        // company simply isn't found here. The activator locks the row so a
        // webhook arriving at the same time cannot activate it twice.
        $payment = Payment::where('razorpay_order_id', $data['razorpay_order_id'])->first();

        if (! $payment) {
            $result = 'missing';
        } elseif ($payment->isPaid()) {
            $result = 'already_paid';
        } else {
            $valid = $this->razorpay->verifyPaymentSignature(
                $data['razorpay_order_id'],
                $data['razorpay_payment_id'],
                $data['razorpay_signature'],
            ) && $this->razorpay->verifyPaymentDetails(
                $data['razorpay_order_id'],
                $data['razorpay_payment_id'],
                $payment->amountInPaise(),
                $payment->currency,
            );

            if (! $valid) {
                $this->activator->recordFailure(
                    $data['razorpay_order_id'],
                    $data['razorpay_payment_id'],
                    'Payment verification failed.',
                );
                $result = 'invalid';
            } else {
                $result = $this->activator->fulfill(
                    $payment,
                    $data['razorpay_payment_id'],
                    $data['razorpay_signature'],
                );
            }
        }

        if ($result === 'missing') {
            return redirect()
                ->route('subscription.plans')
                ->with('error', 'We could not match this payment. Please contact support if you were charged.');
        }

        if ($result === 'invalid') {
            return redirect()
                ->route('subscription.plans')
                ->with('error', 'Payment verification failed. You have not been charged, or any charge will be reversed.');
        }

        if ($result === 'already_paid') {
            return redirect()->route('dashboard')->with('status', 'Your subscription is active.');
        }

        return redirect()
            ->route('dashboard')
            ->with('status', 'Payment successful — your subscription is now active. Thank you!');
    }

    /** Only owners and admins may manage billing. */
    private function authorizeBilling(Request $request): void
    {
        abort_unless($request->user()->isAdmin(), 403, 'Only an owner or admin can manage billing.');
    }
}
