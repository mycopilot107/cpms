<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SuperAdminCompanyConsoleTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('platform.companies.index'))->assertRedirect(route('login'));
    }

    public function test_regular_company_admin_is_forbidden(): void
    {
        $owner = User::factory()->owner()->create(['email' => 'owner@acme.test']);

        $this->actingAs($owner)
            ->get(route('platform.companies.index'))
            ->assertForbidden();
    }

    public function test_super_admin_sees_all_companies_with_expiry(): void
    {
        $super = User::factory()->owner()->create(['email' => 'boss@bestcpms.com', 'is_admin' => true]);

        $subscribed = Company::factory()->create(['name' => 'Acme Towers']);
        Subscription::factory()->create([
            'company_id' => $subscribed->id,
            'plan' => Subscription::PLAN_PAID,
            'status' => Subscription::STATUS_ACTIVE,
            'seats' => 20,
            'billing_months' => 12,
            'price_per_user' => 1000,
            'amount' => 240000,
            'currency' => 'INR',
            'starts_at' => now()->subMonth(),
            'ends_at' => now()->addDays(30),
        ]);

        $lapsed = Company::factory()->trialExpired()->create(['name' => 'Old Build Co']);

        $this->actingAs($super)
            ->get(route('platform.companies.index'))
            ->assertOk()
            // Cross-tenant: companies that are NOT the super admin's own are listed.
            ->assertSee('Acme Towers')
            ->assertSee('Old Build Co')
            ->assertSee('Subscribed')
            ->assertSee('Expired')
            // The active subscription's end date is the shown expiry.
            ->assertSee(now()->addDays(30)->format('j M Y'));
    }

    public function test_super_admin_can_view_a_company_detail(): void
    {
        $super = User::factory()->owner()->create(['email' => 'boss@bestcpms.com', 'is_admin' => true]);

        $company = Company::factory()->create(['name' => 'Acme Towers']);
        Subscription::factory()->create([
            'company_id' => $company->id,
            'plan' => Subscription::PLAN_PAID,
            'status' => Subscription::STATUS_ACTIVE,
            'seats' => 20,
            'billing_months' => 12,
            'price_per_user' => 1000,
            'amount' => 240000,
            'currency' => 'INR',
            'starts_at' => now()->subMonth(),
            'ends_at' => now()->addDays(30),
        ]);

        $this->actingAs($super)
            ->get(route('platform.companies.show', $company))
            ->assertOk()
            ->assertSee('Subscription history')
            ->assertSee('INR 240,000');
    }

    public function test_super_admin_access_is_not_blocked_by_their_own_expiry(): void
    {
        // The super admin's own company has a fully lapsed trial. The console
        // lives outside the subscription gate, so they can still reach it.
        $ownCompany = Company::factory()->trialExpired()->create();
        $super = User::factory()->owner()->forCompany($ownCompany)->create([
            'email' => 'boss@bestcpms.com',
            'is_admin' => true,
        ]);

        $this->actingAs($super)
            ->get(route('platform.companies.index'))
            ->assertOk();
    }

    public function test_admin_flag_grants_platform_access_regardless_of_email(): void
    {
        $super = User::factory()->owner()->create([
            'email' => 'any-admin-address@example.com',
            'is_admin' => true,
        ]);

        $this->actingAs($super)
            ->get(route('platform.companies.index'))
            ->assertOk();
    }
}
