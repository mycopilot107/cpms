<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Thin wrapper over the Razorpay REST API.
 *
 * Deliberately has NO Composer dependency (no razorpay/razorpay SDK): it talks
 * to the public REST endpoints with Laravel's HTTP client and verifies the
 * checkout signature with hash_hmac. This mirrors the app's custom token guard
 * (no Sanctum) and keeps the build free of packages that must be installed.
 *
 * Keys are read only from config (services.razorpay.*), which reads .env — no
 * credential ever lives in source.
 */
class RazorpayService
{
    private const ORDERS_ENDPOINT = 'https://api.razorpay.com/v1/orders';

    private const PAYMENTS_ENDPOINT = 'https://api.razorpay.com/v1/payments';

    private ?string $keyId;

    private ?string $keySecret;

    private ?string $webhookSecret;

    public function __construct()
    {
        $this->keyId = config('services.razorpay.key');
        $this->keySecret = config('services.razorpay.secret');
        $this->webhookSecret = config('services.razorpay.webhook_secret');
    }

    /** True only when both keys are present, so the UI can degrade gracefully. */
    public function configured(): bool
    {
        return ! empty($this->keyId) && ! empty($this->keySecret);
    }

    /** Public key id — safe to expose to the browser for Checkout. */
    public function keyId(): ?string
    {
        return $this->keyId;
    }

    /**
     * Create a Razorpay order.
     *
     * @param  int  $amountInPaise  total charge in the smallest currency unit
     * @return array{id:string,amount:int,currency:string,status:string,receipt:?string}
     *
     * @throws RuntimeException when the gateway is unconfigured or rejects the request
     */
    public function createOrder(int $amountInPaise, string $currency, string $receipt, array $notes = []): array
    {
        if (! $this->configured()) {
            throw new RuntimeException('Razorpay is not configured. Set RAZORPAY_KEY and RAZORPAY_SECRET.');
        }

        $response = Http::withBasicAuth($this->keyId, $this->keySecret)
            ->acceptJson()
            ->asJson()
            ->connectTimeout(5)
            ->timeout(20)
            ->post(self::ORDERS_ENDPOINT, [
                'amount' => $amountInPaise,
                'currency' => $currency,
                'receipt' => $receipt,
                'payment_capture' => 1, // auto-capture on success
                'notes' => $notes,
            ]);

        if ($response->failed()) {
            $message = $response->json('error.description') ?? 'Unable to create the Razorpay order.';
            throw new RuntimeException($message);
        }

        return $response->json();
    }

    /**
     * Verify the signature Razorpay Checkout returns to the browser.
     *
     * expected = HMAC-SHA256("{order_id}|{payment_id}", key_secret)
     * Uses hash_equals for a timing-safe comparison.
     */
    public function verifyPaymentSignature(string $orderId, string $paymentId, string $signature): bool
    {
        if (! $this->configured()) {
            return false;
        }

        $expected = hash_hmac('sha256', $orderId.'|'.$paymentId, $this->keySecret);

        return hash_equals($expected, $signature);
    }

    /**
     * Confirm the payment against Razorpay's server-side records.
     *
     * Checkout data is supplied by the browser, so a valid signature alone is
     * not sufficient. The payment must belong to this order, be captured, and
     * match the amount and currency calculated by the application. The order is
     * fetched as well so a payment cannot be replayed against a different or
     * otherwise invalid order.
     */
    public function verifyPaymentDetails(
        string $orderId,
        string $paymentId,
        int $expectedAmountInPaise,
        string $expectedCurrency,
    ): bool {
        if (! $this->configured()) {
            return false;
        }

        $client = Http::withBasicAuth($this->keyId, $this->keySecret)
            ->acceptJson()
            ->connectTimeout(5)
            ->timeout(20)
            ->retry(2, 200, throw: false);

        $order = $client->get(self::ORDERS_ENDPOINT.'/'.$orderId);
        $payment = $client->get(self::PAYMENTS_ENDPOINT.'/'.$paymentId);

        if ($order->failed() || $payment->failed()) {
            return false;
        }

        $orderData = $order->json();
        $paymentData = $payment->json();

        return ($orderData['id'] ?? null) === $orderId
            && (int) ($orderData['amount'] ?? 0) === $expectedAmountInPaise
            && (string) ($orderData['currency'] ?? '') === $expectedCurrency
            && ($paymentData['id'] ?? null) === $paymentId
            && ($paymentData['order_id'] ?? null) === $orderId
            && ($paymentData['status'] ?? null) === 'captured'
            && (int) ($paymentData['amount'] ?? 0) === $expectedAmountInPaise
            && (string) ($paymentData['currency'] ?? '') === $expectedCurrency;
    }

    /** True when a webhook secret is configured. Checkout keys are not reused. */
    public function webhookConfigured(): bool
    {
        return ! empty($this->webhookSecret);
    }

    /**
     * Verify the raw-body signature Razorpay sends as X-Razorpay-Signature.
     */
    public function verifyWebhookSignature(string $payload, string $signature): bool
    {
        if (! $this->webhookConfigured() || $signature === '') {
            return false;
        }

        $expected = hash_hmac('sha256', $payload, $this->webhookSecret);

        return hash_equals($expected, $signature);
    }
}
