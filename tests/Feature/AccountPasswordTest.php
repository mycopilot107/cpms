<?php

namespace Tests\Feature;

use App\Models\ApiToken;
use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AccountPasswordTest extends TestCase
{
    use RefreshDatabase;

    private const NEW_PASSWORD = 'New-strong-pass-456';

    public function test_super_admin_changes_password_with_the_current_one(): void
    {
        $admin = User::factory()->owner()->create(['is_admin' => true]);

        $this->actingAs($admin)
            ->put(route('account.password.update'), [
                'current_password' => 'password',
                'password' => self::NEW_PASSWORD,
                'password_confirmation' => self::NEW_PASSWORD,
            ])
            ->assertRedirect(route('account.password'))
            ->assertSessionHasNoErrors();

        $this->assertTrue(Hash::check(self::NEW_PASSWORD, $admin->refresh()->password));
    }

    public function test_wrong_current_password_is_rejected(): void
    {
        $admin = User::factory()->owner()->create(['is_admin' => true]);

        $this->actingAs($admin)
            ->put(route('account.password.update'), [
                'current_password' => 'not-my-password',
                'password' => self::NEW_PASSWORD,
                'password_confirmation' => self::NEW_PASSWORD,
            ])
            ->assertSessionHasErrors(['current_password' => 'Your current password is not correct.']);

        $this->assertTrue(Hash::check('password', $admin->refresh()->password));
    }

    public function test_new_password_must_be_confirmed(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->put(route('account.password.update'), [
                'current_password' => 'password',
                'password' => self::NEW_PASSWORD,
                'password_confirmation' => 'something-else-789',
            ])
            ->assertSessionHasErrors('password');
    }

    public function test_changing_password_signs_out_the_mobile_app(): void
    {
        $user = User::factory()->create();
        [$token] = ApiToken::issue($user, 'phone');

        $this->actingAs($user)->put(route('account.password.update'), [
            'current_password' => 'password',
            'password' => self::NEW_PASSWORD,
            'password_confirmation' => self::NEW_PASSWORD,
        ]);

        $this->assertNotNull($token->refresh()->revoked_at);
    }

    public function test_social_only_account_can_set_a_first_password(): void
    {
        $user = User::factory()->create();
        $user->forceFill(['password' => null, 'provider' => 'google', 'provider_id' => 'g-1'])->save();

        $this->actingAs($user)
            ->put(route('account.password.update'), [
                'password' => self::NEW_PASSWORD,
                'password_confirmation' => self::NEW_PASSWORD,
            ])
            ->assertSessionHasNoErrors();

        $this->assertTrue(Hash::check(self::NEW_PASSWORD, $user->refresh()->password));
    }

    public function test_page_opens_even_when_the_company_trial_has_ended(): void
    {
        $company = Company::factory()->trialExpired()->create();
        $user = User::factory()->owner()->forCompany($company)->create();

        $this->actingAs($user)->get(route('account.password'))->assertOk()->assertSee('Change password');
    }

    public function test_guest_is_sent_to_login(): void
    {
        $this->get(route('account.password'))->assertRedirect(route('login'));
    }
}
