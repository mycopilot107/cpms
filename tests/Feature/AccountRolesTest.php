<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class AccountRolesTest extends TestCase
{
    use RefreshDatabase;

    private const NEW_PASSWORD = 'Role-strong-pass-789';

    /**
     * @return array<string, array{string}>
     */
    public static function roles(): array
    {
        return [
            'owner' => [User::ROLE_OWNER],
            'admin' => [User::ROLE_ADMIN],
            'manager' => [User::ROLE_MANAGER],
            'engineer' => [User::ROLE_ENGINEER],
            'supervisor' => [User::ROLE_SUPERVISOR],
            'accountant' => [User::ROLE_ACCOUNTANT],
            'worker' => [User::ROLE_WORKER],
        ];
    }

    private function userWithRole(string $role): User
    {
        return User::factory()->create(['role_id' => Role::idFor($role)]);
    }

    #[DataProvider('roles')]
    public function test_every_role_opens_my_account_pages(string $role): void
    {
        $user = $this->userWithRole($role);

        $this->actingAs($user)->get(route('account.profile'))->assertOk()->assertSee('Save profile');
        $this->actingAs($user)->get(route('account.password'))->assertOk()->assertSee('Change password');
    }

    #[DataProvider('roles')]
    public function test_every_role_changes_their_password(string $role): void
    {
        $user = $this->userWithRole($role);

        $this->actingAs($user)
            ->put(route('account.password.update'), [
                'current_password' => 'password',
                'password' => self::NEW_PASSWORD,
                'password_confirmation' => self::NEW_PASSWORD,
            ])
            ->assertSessionHasNoErrors();

        $this->assertTrue(Hash::check(self::NEW_PASSWORD, $user->refresh()->password));
    }

    #[DataProvider('roles')]
    public function test_every_role_updates_their_profile_without_changing_role(string $role): void
    {
        $user = $this->userWithRole($role);

        $this->actingAs($user)
            ->put(route('account.profile.update'), [
                'name' => 'Renamed Person',
                'email' => $user->email,
                'phone' => '+91 99999 00000',
                'role' => User::ROLE_OWNER,
            ])
            ->assertRedirect(route('account.profile'));

        $user->refresh();
        $this->assertSame('Renamed Person', $user->name);
        $this->assertSame('+91 99999 00000', $user->phone);
        $this->assertSame(Role::idFor($role), $user->role_id);
    }

    public function test_platform_admin_updates_their_profile(): void
    {
        $admin = User::factory()->owner()->create(['is_admin' => true]);

        $this->actingAs($admin)
            ->get(route('account.profile'))
            ->assertOk()
            ->assertSee('Platform admin');

        $this->actingAs($admin)
            ->put(route('account.profile.update'), ['name' => 'Chief Admin', 'email' => $admin->email])
            ->assertSessionHasNoErrors();

        $this->assertSame('Chief Admin', $admin->refresh()->name);
    }

    public function test_changing_email_requires_the_current_password(): void
    {
        $user = $this->userWithRole(User::ROLE_WORKER);

        $this->actingAs($user)
            ->put(route('account.profile.update'), ['name' => $user->name, 'email' => 'new@site.test'])
            ->assertSessionHasErrors(['current_password' => 'Enter your current password to change your email.']);

        $this->actingAs($user)
            ->put(route('account.profile.update'), ['name' => $user->name, 'email' => 'new@site.test', 'current_password' => 'password'])
            ->assertSessionHasNoErrors();

        $this->assertSame('new@site.test', $user->refresh()->email);
    }

    public function test_email_already_used_by_another_account_is_rejected(): void
    {
        User::factory()->create(['email' => 'taken@site.test']);
        $user = $this->userWithRole(User::ROLE_ENGINEER);

        $this->actingAs($user)
            ->put(route('account.profile.update'), ['name' => $user->name, 'email' => 'taken@site.test', 'current_password' => 'password'])
            ->assertSessionHasErrors('email');
    }

    /**
     * @return array<string, array{string}>
     */
    public static function nonAdminRoles(): array
    {
        return [
            'manager' => [User::ROLE_MANAGER],
            'engineer' => [User::ROLE_ENGINEER],
            'supervisor' => [User::ROLE_SUPERVISOR],
            'accountant' => [User::ROLE_ACCOUNTANT],
            'worker' => [User::ROLE_WORKER],
        ];
    }

    #[DataProvider('nonAdminRoles')]
    public function test_non_admin_cannot_promote_themselves_through_the_team_form(string $role): void
    {
        $user = $this->userWithRole($role);

        $this->actingAs($user)
            ->put(route('users.update', $user), [
                'name' => $user->name,
                'email' => $user->email,
                'role' => User::ROLE_ADMIN,
                'status' => 'active',
            ])
            ->assertForbidden();

        $this->assertSame(Role::idFor($role), $user->refresh()->role_id);
    }
}
