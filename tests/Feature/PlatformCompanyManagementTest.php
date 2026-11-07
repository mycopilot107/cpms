<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Role;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class PlatformCompanyManagementTest extends TestCase
{
    use RefreshDatabase;

    private function superAdmin(): User
    {
        return User::factory()->owner()->create(['email' => 'boss@bestcpms.com', 'is_admin' => true]);
    }

    /**
     * @return array<string, mixed>
     */
    private function newCompanyPayload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Skyline Builders',
            'email' => 'office@skyline.test',
            'phone' => '+971 4 000 0000',
            'country' => 'United Arab Emirates',
            'currency' => 'AED',
            'city' => 'Dubai',
            'trial_days' => 30,
            'owner_name' => 'Omar Khan',
            'owner_email' => 'omar@skyline.test',
            'owner_password' => 'Strong-pass-123',
        ], $overrides);
    }

    public function test_super_admin_adds_a_company_with_owner_and_trial(): void
    {
        $this->freezeTime();

        $response = $this->actingAs($this->superAdmin())
            ->post(route('platform.companies.store'), $this->newCompanyPayload());

        $company = Company::query()->where('email', 'office@skyline.test')->firstOrFail();
        $response->assertRedirect(route('platform.companies.show', $company));

        $this->assertSame('AED', $company->currency);
        $this->assertSame(now()->addDays(30)->toDateTimeString(), $company->trial_ends_at->toDateTimeString());

        $owner = User::query()->where('email', 'omar@skyline.test')->firstOrFail();
        $this->assertSame($company->id, $owner->company_id);
        $this->assertSame(Role::idFor(User::ROLE_OWNER), $owner->role_id);
        $this->assertDatabaseHas('subscriptions', [
            'company_id' => $company->id,
            'status' => Subscription::STATUS_TRIAL,
        ]);
    }

    public function test_adding_a_company_rejects_an_owner_email_already_in_use(): void
    {
        User::factory()->create(['email' => 'omar@skyline.test']);

        $this->actingAs($this->superAdmin())
            ->post(route('platform.companies.store'), $this->newCompanyPayload())
            ->assertSessionHasErrors('owner_email');

        $this->assertDatabaseMissing('companies', ['email' => 'office@skyline.test']);
    }

    public function test_super_admin_edits_profile_and_extends_the_trial(): void
    {
        $company = Company::factory()->trialExpired()->create(['name' => 'Old Name']);

        $this->actingAs($this->superAdmin())
            ->put(route('platform.companies.update', $company), [
                'name' => 'New Name',
                'email' => $company->email,
                'country' => 'India',
                'currency' => 'INR',
                'status' => Company::STATUS_ACTIVE,
                'trial_ends_at' => now()->addDays(10)->format('Y-m-d'),
            ])
            ->assertRedirect(route('platform.companies.show', $company));

        $company->refresh();
        $this->assertSame('New Name', $company->name);
        $this->assertTrue($company->isOnTrial());
    }

    public function test_suspending_a_company_blocks_its_users_on_web_and_api(): void
    {
        $company = Company::factory()->create();
        $member = User::factory()->forCompany($company)->create();

        $this->actingAs($this->superAdmin())
            ->put(route('platform.companies.update', $company), [
                'name' => $company->name,
                'email' => $company->email,
                'country' => 'India',
                'currency' => 'INR',
                'status' => Company::STATUS_SUSPENDED,
                'trial_ends_at' => $company->trial_ends_at->format('Y-m-d'),
            ])
            ->assertRedirect();

        $this->actingAs($member)
            ->get(route('dashboard'))
            ->assertRedirect(route('subscription.expired'));

        $this->actingAs($member)
            ->get(route('subscription.expired'))
            ->assertOk()
            ->assertSee('This company account is suspended');

        $this->postJson('/api/login', ['email' => $member->email, 'password' => 'password'])
            ->assertUnprocessable();
    }

    public function test_company_owner_cannot_use_platform_management(): void
    {
        $owner = User::factory()->owner()->create();
        $company = $owner->company;

        $this->actingAs($owner)->get(route('platform.companies.create'))->assertForbidden();
        $this->actingAs($owner)->post(route('platform.companies.store'), $this->newCompanyPayload())->assertForbidden();
        $this->actingAs($owner)->put(route('platform.companies.update', $company), ['status' => 'active'])->assertForbidden();
        $this->actingAs($owner)->get(route('platform.smtp-test'))->assertForbidden();
    }

    public function test_smtp_page_shows_settings_without_the_password(): void
    {
        config(['mail.mailers.smtp.password' => 'super-secret-password']);

        $this->actingAs($this->superAdmin())
            ->get(route('platform.smtp-test'))
            ->assertOk()
            ->assertSee('MAIL_HOST')
            ->assertSee('set (hidden)')
            ->assertDontSee('super-secret-password');
    }

    public function test_smtp_test_reports_success(): void
    {
        Mail::fake();

        $this->actingAs($this->superAdmin())
            ->post(route('platform.smtp-test.send'), ['to_email' => 'check@example.test'])
            ->assertRedirect()
            ->assertSessionHas('smtp_result.success', true);
    }
}
