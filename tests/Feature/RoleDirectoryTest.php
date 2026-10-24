<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleDirectoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_features_page_lists_every_role(): void
    {
        $this->get('/features')
            ->assertOk()
            ->assertSee('<table', false)
            ->assertSee('Owner')
            ->assertSee('Project manager')
            ->assertSee('Site worker')
            ->assertSee('Registers the company and holds the account.', false)
            ->assertDontSee('Runs the company day to day', false);
    }

    public function test_how_it_works_page_explains_roles_without_admin(): void
    {
        $this->get('/how-it-works')
            ->assertOk()
            ->assertSee('Roles, and where each one signs in')
            ->assertSee('Project manager')
            ->assertSee('Site worker')
            ->assertSee('Android app')
            ->assertDontSee('Runs the company day to day', false);
    }

    public function test_new_user_stores_the_worker_role_id(): void
    {
        $user = User::factory()->create();

        $this->assertSame(Role::idFor(User::ROLE_WORKER), $user->role_id);
        $this->assertSame(User::ROLE_WORKER, $user->role?->slug);
    }
}
