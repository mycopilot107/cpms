<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_an_admin_creates_a_role_and_that_role_only_opens_its_pages(): void
    {
        $admin = User::factory()->create(['role_id' => Role::idFor(User::ROLE_ADMIN)]);

        $this->actingAs($admin)
            ->post(route('roles.store'), [
                'name' => 'Accounts clerk',
                'description' => 'Enters bills and reads the cost reports.',
                'pages' => ['finance', 'reports'],
            ])
            ->assertRedirect(route('roles.index'));

        $role = Role::query()->where('name', 'Accounts clerk')->first();
        $this->assertNotNull($role);
        $this->assertSame($admin->company_id, $role->company_id);
        $this->assertSame(['finance', 'reports'], $role->pageKeys());

        $clerk = User::factory()->forCompany($admin->company)->create(['role_id' => $role->id]);

        $this->actingAs($clerk)->get(route('finance.index'))->assertOk();
        $this->actingAs($clerk)->get(route('reports.index'))->assertOk();
        $this->actingAs($clerk)->get(route('projects.index'))->assertForbidden();
        $this->actingAs($clerk)->get(route('users.index'))->assertForbidden();
        $this->actingAs($clerk)->get(route('roles.index'))->assertForbidden();

        $this->get('/features')->assertOk()->assertDontSee('Accounts clerk');
    }

    public function test_a_built_in_role_cannot_be_edited_or_deleted(): void
    {
        $admin = User::factory()->create(['role_id' => Role::idFor(User::ROLE_ADMIN)]);
        $worker = Role::query()->where('slug', User::ROLE_WORKER)->firstOrFail();

        $this->actingAs($admin)->get(route('roles.edit', $worker))->assertNotFound();
        $this->actingAs($admin)->delete(route('roles.destroy', $worker))->assertNotFound();
        $this->assertNotNull(Role::query()->find($worker->id));
    }

    public function test_a_role_assigned_to_someone_cannot_be_deleted(): void
    {
        $admin = User::factory()->create(['role_id' => Role::idFor(User::ROLE_ADMIN)]);
        $role = $this->roleFor($admin, 'Store keeper', ['tasks']);
        User::factory()->forCompany($admin->company)->create(['role_id' => $role->id]);

        $this->actingAs($admin)
            ->from(route('roles.index'))
            ->delete(route('roles.destroy', $role))
            ->assertRedirect(route('roles.index'))
            ->assertSessionHasErrors('role');

        $this->assertNotNull($role->fresh());
    }

    public function test_another_company_cannot_edit_the_role(): void
    {
        $admin = User::factory()->create(['role_id' => Role::idFor(User::ROLE_ADMIN)]);
        $role = $this->roleFor($admin, 'Buyer', ['projects']);
        $outsider = User::factory()->create(['role_id' => Role::idFor(User::ROLE_ADMIN)]);

        $this->actingAs($outsider)
            ->put(route('roles.update', $role), [
                'name' => 'Taken over',
                'description' => 'Should not save.',
                'pages' => ['billing'],
            ])
            ->assertNotFound();

        $this->assertSame('Buyer', $role->fresh()->name);
    }

    public function test_a_worker_still_cannot_open_finance(): void
    {
        $worker = User::factory()->create(['role_id' => Role::idFor(User::ROLE_WORKER)]);

        $this->actingAs($worker)->get(route('finance.index'))->assertForbidden();
        $this->actingAs($worker)->get(route('dashboard'))->assertOk();
    }

    private function roleFor(User $admin, string $name, array $pages): Role
    {
        $role = new Role([
            'name' => $name,
            'description' => $name,
            'slug' => str($name)->slug().'-c'.$admin->company_id,
            'pages' => $pages,
            'sort_order' => 50,
        ]);
        $role->company_id = $admin->company_id;
        $role->save();

        return $role;
    }
}
