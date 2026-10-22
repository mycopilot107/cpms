<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\Subscription;
use Illuminate\Support\Facades\DB;

/**
 * Turns a verified Razorpay capture into company access.
 *
 * Used by the browser callback and the webhook so a captured payment still
 * activates the plan when the shopper closes the Checkout window. A renewal
 * extends the current end date instead of discarding days already paid for,
 * and updates the seat count immediately.
 */
class SubscriptionActivator
{
    /**
     * @return 'paid'|'already_paid'
     */
    public function fulfill(Payment $payment, string $razorpayPaymentId, ?string $signature = null): string
    {
        return DB::transaction(function () use ($payment, $razorpayPaymentId, $signature): string {
            $locked = Payment::query()
                ->whereKey($payment->id)
                ->lockForUpdate()
                ->first();

            if ($locked === null) {
                return 'already_paid';
            }

            if ($locked->isPaid()) {
                return 'already_paid';
            }

            $subscription = $this->grant($locked);

            $locked->update([
                'status' => Payment::STATUS_PAID,
                'razorpay_payment_id' => $razorpayPaymentId,
                'razorpay_signature' => $signature,
                'subscription_id' => $subscription->id,
                'failure_reason' => null,
            ]);

            return 'paid';
        });
    }

    /**
     * Record a gateway failure without touching a payment that already succeeded.
     */
    public function recordFailure(string $orderId, string $paymentId, string $reason): void
    {
        if ($orderId === '') {
            return;
        }

        DB::transaction(function () use ($orderId, $paymentId, $reason): void {
            $payment = Payment::query()
                ->where('razorpay_order_id', $orderId)
                ->lockForUpdate()
                ->first();

            if ($payment === null || $payment->isPaid()) {
                return;
            }

            $payment->update([
                'status' => Payment::STATUS_FAILED,
                'razorpay_payment_id' => $paymentId !== '' ? $paymentId : $payment->razorpay_payment_id,
                'failure_reason' => $reason,
            ]);
        });
    }

    private function grant(Payment $payment): Subscription
    {
        $existing = Subscription::query()
            ->where('company_id', $payment->company_id)
            ->where('status', Subscription::STATUS_ACTIVE)
            ->where('ends_at', '>', now())
            ->orderByDesc('ends_at')
            ->lockForUpdate()
            ->first();

        if ($existing !== null) {
            $existing->forceFill([
                'plan' => Subscription::PLAN_PAID,
                'plan_id' => $payment->plan_id,
                'billing_period' => $payment->billing_period,
                'seats' => $payment->seats,
                'billing_months' => $payment->months,
                'price_per_user' => $payment->price_per_user,
                'amount' => $payment->amount,
                'currency' => $payment->currency,
                'ends_at' => $existing->ends_at->copy()->addMonths($payment->months),
            ])->save();

            return $existing;
        }

        return Subscription::create([
            'company_id' => $payment->company_id,
            'plan' => Subscription::PLAN_PAID,
            'plan_id' => $payment->plan_id,
            'billing_period' => $payment->billing_period,
            'status' => Subscription::STATUS_ACTIVE,
            'seats' => $payment->seats,
            'billing_months' => $payment->months,
            'price_per_user' => $payment->price_per_user,
            'amount' => $payment->amount,
            'currency' => $payment->currency,
            'starts_at' => now(),
            'ends_at' => now()->addMonths($payment->months),
        ]);
    }
}
