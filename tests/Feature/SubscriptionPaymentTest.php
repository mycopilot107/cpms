<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Payment;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class SubscriptionPaymentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.razorpay.key' => 'rzp_test_key',
            'services.razorpay.secret' => 'rzp_test_secret',
            'services.razorpay.webhook_secret' => 'whsec_test',
        ]);
    }

    public function test_owner_checkout_creates_a_server_priced_order(): void
    {
        Http::preventStrayRequests();
        Http::fake([
            'https://api.razorpay.com/v1/orders' => Http::response([
                'id' => 'order_test_1',
                'amount' => 200000,
                'currency' => 'INR',
                'status' => 'created',
            ]),
        ]);

        $owner = User::factory()->owner()->create();

        $this->actingAs($owner)
            ->post(route('subscription.checkout'), ['seats' => 2, 'months' => 1])
            ->assertOk()
            ->assertSee('order_test_1');

        $this->assertDatabaseHas('payments', [
            'company_id' => $owner->company_id,
            'seats' => 2,
            'months' => 1,
            'amount' => 2000,
            'currency' => 'INR',
            'status' => Payment::STATUS_CREATED,
            'razorpay_order_id' => 'order_test_1',
        ]);
    }

    public function test_worker_cannot_open_billing(): void
    {
        $worker = User::factory()->create();

        $this->actingAs($worker)
            ->get(route('subscription.plans'))
            ->assertForbidden();
    }

    public function test_verified_checkout_activates_a_paid_subscription(): void
    {
        $this->freezeTime();
        $owner = User::factory()->owner()->create();
        $payment = $this->openPayment($owner, 'order_ok', 2, 1, 2000);
        $this->fakeCapturedPayment('order_ok', 'pay_ok', 200000);

        $this->actingAs($owner)
            ->post(route('subscription.verify'), [
                'razorpay_order_id' => 'order_ok',
                'razorpay_payment_id' => 'pay_ok',
                'razorpay_signature' => $this->checkoutSignature('order_ok', 'pay_ok'),
            ])
            ->assertRedirect(route('dashboard'));

        $payment->refresh();
        $this->assertTrue($payment->isPaid());
        $this->assertNotNull($payment->subscription_id);

        $subscription = Subscription::query()->findOrFail($payment->subscription_id);
        $this->assertSame(Subscription::STATUS_ACTIVE, $subscription->status);
        $this->assertSame(2, $subscription->seats);
        $this->assertSame(now()->addMonths(1)->toDateTimeString(), $subscription->ends_at->toDateTimeString());
    }

    public function test_renewal_extends_the_current_end_date(): void
    {
        $this->freezeTime();
        $company = Company::factory()->trialExpired()->create();
        $owner = User::factory()->owner()->forCompany($company)->create();
        $existing = Subscription::factory()->create([
            'company_id' => $company->id,
            'plan' => Subscription::PLAN_PAID,
            'status' => Subscription::STATUS_ACTIVE,
            'seats' => 2,
            'billing_months' => 1,
            'price_per_user' => 1000,
            'amount' => 2000,
            'starts_at' => now()->subDays(10),
            'ends_at' => now()->addDays(20),
        ]);

        $payment = $this->openPayment($owner, 'order_renew', 5, 3, 15000);
        $this->fakeCapturedPayment('order_renew', 'pay_renew', 1500000);

        $this->actingAs($owner)->post(route('subscription.verify'), [
            'razorpay_order_id' => 'order_renew',
            'razorpay_payment_id' => 'pay_renew',
            'razorpay_signature' => $this->checkoutSignature('order_renew', 'pay_renew'),
        ])->assertRedirect(route('dashboard'));

        $existing->refresh();
        $this->assertSame(5, $existing->seats);
        $this->assertSame(now()->addDays(20)->addMonths(3)->toDateTimeString(), $existing->ends_at->toDateTimeString());
        $this->assertSame(1, Subscription::query()->where('company_id', $company->id)->count());
    }

    public function test_invalid_checkout_signature_does_not_activate_the_plan(): void
    {
        $owner = User::factory()->owner()->create();
        $payment = $this->openPayment($owner, 'order_bad', 2, 1, 2000);

        $this->actingAs($owner)->post(route('subscription.verify'), [
            'razorpay_order_id' => 'order_bad',
            'razorpay_payment_id' => 'pay_bad',
            'razorpay_signature' => 'not-a-real-signature',
        ])->assertRedirect(route('subscription.plans'));

        $this->assertSame(Payment::STATUS_FAILED, $payment->fresh()->status);
        $this->assertSame(0, Subscription::query()->where('plan', Subscription::PLAN_PAID)->count());
    }

    public function test_captured_webhook_activates_the_plan_without_a_browser_callback(): void
    {
        $this->freezeTime();
        Http::preventStrayRequests();
        $owner = User::factory()->owner()->create();
        $payment = $this->openPayment($owner, 'order_hook', 2, 1, 2000);
        $this->fakeCapturedPayment('order_hook', 'pay_hook', 200000);

        $this->postWebhook('payment.captured', [
            'id' => 'pay_hook',
            'order_id' => 'order_hook',
            'amount' => 200000,
            'currency' => 'INR',
            'status' => 'captured',
        ])->assertOk()->assertJsonPath('status', 'paid');

        $this->assertTrue($payment->fresh()->isPaid());
        $this->assertTrue($owner->company->fresh()->hasActiveSubscription());
    }

    public function test_webhook_returns_400_when_the_signature_is_wrong(): void
    {
        $this->postJson('/api/webhooks/razorpay', ['event' => 'payment.captured'], [
            'X-Razorpay-Signature' => 'nope',
        ])->assertStatus(400);
    }

    public function test_webhook_does_not_activate_a_payment_whose_amount_does_not_match(): void
    {
        Http::preventStrayRequests();
        $owner = User::factory()->owner()->create();
        $payment = $this->openPayment($owner, 'order_mismatch', 2, 1, 2000);
        $this->fakeCapturedPayment('order_mismatch', 'pay_mismatch', 100);

        $this->postWebhook('payment.captured', [
            'id' => 'pay_mismatch',
            'order_id' => 'order_mismatch',
            'amount' => 100,
            'currency' => 'INR',
            'status' => 'captured',
        ])->assertOk()->assertJsonPath('status', 'mismatch');

        $this->assertSame(Payment::STATUS_FAILED, $payment->fresh()->status);
        $this->assertFalse($owner->company->fresh()->hasActiveSubscription());
    }

    public function test_repeated_captured_webhook_does_not_extend_the_plan_again(): void
    {
        $this->freezeTime();
        Http::preventStrayRequests();
        $owner = User::factory()->owner()->create();
        $this->openPayment($owner, 'order_once', 2, 1, 2000);
        $this->fakeCapturedPayment('order_once', 'pay_once', 200000);

        $entity = [
            'id' => 'pay_once',
            'order_id' => 'order_once',
            'amount' => 200000,
            'currency' => 'INR',
            'status' => 'captured',
        ];

        $this->postWebhook('payment.captured', $entity)->assertJsonPath('status', 'paid');
        $endsAt = $owner->company->fresh()->activeSubscription()->ends_at;

        $this->postWebhook('payment.captured', $entity)->assertJsonPath('status', 'already_paid');
        $this->assertSame($endsAt->toDateTimeString(), $owner->company->fresh()->activeSubscription()->ends_at->toDateTimeString());
    }

    private function openPayment(User $owner, string $orderId, int $seats, int $months, int $amount): Payment
    {
        $payment = new Payment([
            'user_id' => $owner->id,
            'seats' => $seats,
            'months' => $months,
            'billing_period' => 'month',
            'price_per_user' => 1000,
            'amount' => $amount,
            'currency' => 'INR',
            'status' => Payment::STATUS_CREATED,
            'receipt' => 'rcpt_'.$orderId,
            'razorpay_order_id' => $orderId,
        ]);
        $payment->company_id = $owner->company_id;
        $payment->save();

        return $payment;
    }

    private function fakeCapturedPayment(string $orderId, string $paymentId, int $amountInPaise): void
    {
        Http::preventStrayRequests();
        Http::fake([
            'https://api.razorpay.com/v1/orders/'.$orderId => Http::response([
                'id' => $orderId,
                'amount' => $amountInPaise,
                'currency' => 'INR',
                'status' => 'paid',
            ]),
            'https://api.razorpay.com/v1/payments/'.$paymentId => Http::response([
                'id' => $paymentId,
                'order_id' => $orderId,
                'amount' => $amountInPaise,
                'currency' => 'INR',
                'status' => 'captured',
            ]),
        ]);
    }

    private function checkoutSignature(string $orderId, string $paymentId): string
    {
        return hash_hmac('sha256', $orderId.'|'.$paymentId, 'rzp_test_secret');
    }

    /**
     * @param  array<string, mixed>  $entity
     */
    private function postWebhook(string $event, array $entity): TestResponse
    {
        $body = json_encode([
            'event' => $event,
            'payload' => ['payment' => ['entity' => $entity]],
        ], JSON_THROW_ON_ERROR);

        return $this->call(
            'POST',
            '/api/webhooks/razorpay',
            [],
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_ACCEPT' => 'application/json',
                'HTTP_X_RAZORPAY_SIGNATURE' => hash_hmac('sha256', $body, 'whsec_test'),
            ],
            $body,
        );
    }
}
