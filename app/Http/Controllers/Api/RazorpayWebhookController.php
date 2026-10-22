<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Services\RazorpayService;
use App\Services\SubscriptionActivator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Razorpay server-to-server callback.
 *
 * Checkout's browser handler can be closed before it posts back. This endpoint
 * is the reliable step: a captured payment still grants access, and a failed
 * payment is recorded. The body is trusted only after the webhook signature
 * matches, and the amount is confirmed against Razorpay's payment record.
 */
class RazorpayWebhookController extends Controller
{
    public function __construct(
        private readonly RazorpayService $razorpay,
        private readonly SubscriptionActivator $activator,
    ) {}

    public function __invoke(Request $request): JsonResponse
    {
        if (! $this->razorpay->webhookConfigured()) {
            return response()->json(['status' => 'unconfigured'], 503);
        }

        $payload = $request->getContent();
        $signature = (string) $request->header('X-Razorpay-Signature', '');

        if (! $this->razorpay->verifyWebhookSignature($payload, $signature)) {
            return response()->json(['status' => 'invalid_signature'], 400);
        }

        $event = (string) $request->json('event');
        /** @var array<string, mixed> $entity */
        $entity = $request->json('payload.payment.entity', []);
        $orderId = (string) ($entity['order_id'] ?? '');
        $paymentId = (string) ($entity['id'] ?? '');

        if ($event === 'payment.failed') {
            $reason = (string) data_get($entity, 'error_description', 'Payment failed at the gateway.');
            $this->activator->recordFailure($orderId, $paymentId, $reason);

            return response()->json(['status' => 'failed']);
        }

        if ($event !== 'payment.captured') {
            return response()->json(['status' => 'ignored']);
        }

        $payment = Payment::query()->where('razorpay_order_id', $orderId)->first();

        if ($payment === null) {
            return response()->json(['status' => 'missing']);
        }

        if ($payment->isPaid()) {
            return response()->json(['status' => 'already_paid']);
        }

        $amount = (int) ($entity['amount'] ?? 0);
        $currency = (string) ($entity['currency'] ?? '');

        $matches = $amount === $payment->amountInPaise()
            && $currency === $payment->currency
            && $this->razorpay->verifyPaymentDetails($orderId, $paymentId, $payment->amountInPaise(), $payment->currency);

        if (! $matches) {
            $this->activator->recordFailure($orderId, $paymentId, 'Captured webhook did not match the order amount.');

            return response()->json(['status' => 'mismatch']);
        }

        $result = $this->activator->fulfill($payment, $paymentId);

        return response()->json(['status' => $result]);
    }
}
