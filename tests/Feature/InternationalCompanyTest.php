<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class InternationalCompanyTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_stores_the_chosen_country_and_currency(): void
    {
        $this->post(route('register.store'), [
            'company_name' => 'Gulf Builders',
            'name' => 'Omar',
            'email' => 'omar@gulf.test',
            'country' => 'United Arab Emirates',
            'currency' => 'AED',
            'password' => 'Secret-pass-123',
            'password_confirmation' => 'Secret-pass-123',
        ])->assertRedirect(route('dashboard'));

        $this->assertDatabaseHas('companies', [
            'name' => 'Gulf Builders',
            'country' => 'United Arab Emirates',
            'currency' => 'AED',
        ]);
    }

    public function test_registration_rejects_an_unknown_currency(): void
    {
        $this->post(route('register.store'), [
            'company_name' => 'Nowhere Ltd',
            'name' => 'Sam',
            'email' => 'sam@nowhere.test',
            'country' => 'United States',
            'currency' => 'XYZ',
            'password' => 'Secret-pass-123',
            'password_confirmation' => 'Secret-pass-123',
        ])->assertSessionHasErrors('currency');

        $this->assertDatabaseMissing('companies', ['name' => 'Nowhere Ltd']);
    }

    public function test_project_budget_is_shown_in_the_company_currency(): void
    {
        $company = Company::factory()->inCountry('United States', 'USD')->create();
        $owner = User::factory()->owner()->forCompany($company)->create();
        $project = Project::factory()->create(['company_id' => $company->id, 'budget' => 2500]);

        $this->actingAs($owner)
            ->get(route('projects.show', $project))
            ->assertOk()
            ->assertSee('$2,500.00')
            ->assertDontSee('₹2,500.00');
    }

    public function test_admin_can_change_company_country_and_currency(): void
    {
        $owner = User::factory()->owner()->create();

        $this->actingAs($owner)
            ->put(route('company.settings.update'), [
                'name' => 'Northern Build Co',
                'country' => 'United Kingdom',
                'currency' => 'GBP',
                'gst_number' => 'GB123456789',
            ])
            ->assertRedirect(route('company.settings'));

        $company = $owner->company->refresh();
        $this->assertSame('United Kingdom', $company->country);
        $this->assertSame('GBP', $company->currency);
        $this->assertSame('GB123456789', $company->gst_number);
    }

    public function test_worker_cannot_change_company_settings(): void
    {
        $worker = User::factory()->create();

        $this->actingAs($worker)->get(route('company.settings'))->assertForbidden();
        $this->actingAs($worker)
            ->put(route('company.settings.update'), [
                'name' => 'Hijacked',
                'country' => 'India',
                'currency' => 'INR',
            ])
            ->assertForbidden();
    }

    public function test_dollar_company_is_billed_in_usd_when_usd_charging_is_on(): void
    {
        config([
            'services.razorpay.key' => 'rzp_test_key',
            'services.razorpay.secret' => 'rzp_test_secret',
            'billing.currencies.USD.charge' => true,
        ]);
        Http::preventStrayRequests();
        Http::fake([
            'https://api.razorpay.com/v1/orders' => Http::response([
                'id' => 'order_usd_1',
                'amount' => 2400,
                'currency' => 'USD',
                'status' => 'created',
            ]),
        ]);
        $company = Company::factory()->inCountry('United States', 'USD')->create();
        $owner = User::factory()->owner()->forCompany($company)->create();

        $this->actingAs($owner)
            ->post(route('subscription.checkout'), ['seats' => 2, 'months' => 1])
            ->assertOk();

        $this->assertDatabaseHas('payments', [
            'company_id' => $company->id,
            'amount' => 24,
            'currency' => 'USD',
            'razorpay_order_id' => 'order_usd_1',
        ]);
        Http::assertSent(fn ($request) => $request['currency'] === 'USD' && $request['amount'] === 2400);
    }

    public function test_dollar_company_is_billed_in_inr_while_usd_charging_is_off(): void
    {
        config([
            'services.razorpay.key' => 'rzp_test_key',
            'services.razorpay.secret' => 'rzp_test_secret',
            'billing.currencies.USD.charge' => false,
        ]);
        Http::preventStrayRequests();
        Http::fake([
            'https://api.razorpay.com/v1/orders' => Http::response([
                'id' => 'order_inr_1',
                'amount' => 200000,
                'currency' => 'INR',
                'status' => 'created',
            ]),
        ]);
        $company = Company::factory()->inCountry('United States', 'USD')->create();
        $owner = User::factory()->owner()->forCompany($company)->create();

        $this->actingAs($owner)
            ->post(route('subscription.checkout'), ['seats' => 2, 'months' => 1])
            ->assertOk();

        $this->assertDatabaseHas('payments', [
            'company_id' => $company->id,
            'amount' => 2000,
            'currency' => 'INR',
        ]);
    }
}
