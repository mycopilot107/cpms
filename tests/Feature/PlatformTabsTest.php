<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\ContactMessage;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlatformTabsTest extends TestCase
{
    use RefreshDatabase;

    private function superAdmin(): User
    {
        return User::factory()->owner()->create(['email' => 'boss@bestcpms.com', 'is_admin' => true]);
    }

    private function payment(Company $company, array $attributes = []): Payment
    {
        $payment = new Payment(array_merge([
            'seats' => 5,
            'months' => 3,
            'billing_period' => 'month',
            'price_per_user' => 1000,
            'amount' => 15000,
            'currency' => 'INR',
            'status' => Payment::STATUS_PAID,
            'receipt' => 'rcpt_'.uniqid(),
            'razorpay_order_id' => 'order_'.uniqid(),
            'razorpay_payment_id' => 'pay_'.uniqid(),
        ], $attributes));
        $payment->forceFill(['company_id' => $company->id])->save();

        return $payment;
    }

    public function test_payments_tab_lists_payments_from_every_company(): void
    {
        $acme = Company::factory()->create(['name' => 'Acme Towers']);
        $gulf = Company::factory()->create(['name' => 'Gulf Builders']);
        $this->payment($acme);
        $this->payment($gulf, ['status' => Payment::STATUS_FAILED, 'failure_reason' => 'Card declined', 'razorpay_payment_id' => null]);

        $this->actingAs($this->superAdmin())
            ->get(route('platform.payments.index'))
            ->assertOk()
            ->assertSee('Acme Towers')
            ->assertSee('Gulf Builders')
            ->assertSee('Card declined');
    }

    public function test_payments_tab_filters_by_status(): void
    {
        $acme = Company::factory()->create(['name' => 'Acme Towers']);
        $gulf = Company::factory()->create(['name' => 'Gulf Builders']);
        $this->payment($acme);
        $this->payment($gulf, ['status' => Payment::STATUS_FAILED]);

        $this->actingAs($this->superAdmin())
            ->get(route('platform.payments.index', ['status' => 'failed']))
            ->assertOk()
            ->assertSee('Gulf Builders')
            ->assertDontSee('Acme Towers');
    }

    public function test_payment_details_show_razorpay_ids_and_company(): void
    {
        $company = Company::factory()->create(['name' => 'Acme Towers']);
        $payment = $this->payment($company, ['razorpay_payment_id' => 'pay_ABC123']);

        $this->actingAs($this->superAdmin())
            ->get(route('platform.payments.show', $payment->id))
            ->assertOk()
            ->assertSee('pay_ABC123')
            ->assertSee('Acme Towers')
            ->assertSee('15,000');
    }

    public function test_payments_export_downloads_a_csv(): void
    {
        $company = Company::factory()->create(['name' => 'Acme Towers']);
        $this->payment($company);

        $response = $this->actingAs($this->superAdmin())
            ->get(route('platform.payments.index', ['export' => 'csv']));

        $response->assertOk()->assertHeader('content-type', 'text/csv; charset=utf-8');
        $this->assertStringContainsString('Acme Towers', $response->streamedContent());
    }

    public function test_company_page_lists_that_company_payments(): void
    {
        $company = Company::factory()->create();
        $this->payment($company, ['razorpay_payment_id' => 'pay_COMPANY1']);

        $this->actingAs($this->superAdmin())
            ->get(route('platform.companies.show', $company))
            ->assertOk()
            ->assertSee('Payments (1)')
            ->assertSee('Paid in total');
    }

    public function test_users_tab_lists_users_across_companies(): void
    {
        User::factory()->create(['name' => 'Ravi Engineer', 'email' => 'ravi@acme.test']);
        User::factory()->create(['name' => 'Omar Khan', 'email' => 'omar@gulf.test']);

        $this->actingAs($this->superAdmin())
            ->get(route('platform.users.index', ['q' => 'omar']))
            ->assertOk()
            ->assertSee('omar@gulf.test')
            ->assertDontSee('ravi@acme.test');
    }

    public function test_opening_a_message_marks_it_read(): void
    {
        $message = ContactMessage::create([
            'type' => ContactMessage::TYPE_CONTACT,
            'name' => 'Priya',
            'email' => 'priya@site.test',
            'subject' => 'Demo please',
            'message' => 'Can we see a demo?',
        ]);
        $admin = $this->superAdmin();

        $this->actingAs($admin)->get(route('platform.messages.index', ['type' => 'unread']))->assertOk()->assertSee('Priya');
        $this->actingAs($admin)->get(route('platform.messages.show', $message))->assertOk()->assertSee('Can we see a demo?');

        $this->assertNotNull($message->refresh()->read_at);
    }

    public function test_overview_shows_revenue_and_counts(): void
    {
        $company = Company::factory()->create();
        $this->payment($company, ['amount' => 24000]);

        $this->actingAs($this->superAdmin())
            ->get(route('platform.overview'))
            ->assertOk()
            ->assertSee('Revenue this month')
            ->assertSee('24,000');
    }

    public function test_company_owner_cannot_open_platform_tabs(): void
    {
        $owner = User::factory()->owner()->create();

        foreach (['platform.overview', 'platform.payments.index', 'platform.users.index', 'platform.messages.index'] as $route) {
            $this->actingAs($owner)->get(route($route))->assertForbidden();
        }
    }

    public function test_super_admin_lands_on_the_overview_after_login(): void
    {
        $admin = $this->superAdmin();

        $this->post(route('login.store'), ['email' => $admin->email, 'password' => 'password'])
            ->assertRedirect(route('platform.overview'));
    }
}
