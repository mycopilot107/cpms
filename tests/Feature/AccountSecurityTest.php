<?php

namespace Tests\Feature;

use App\Http\Controllers\ReportController;
use App\Models\ApiToken;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccountSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_api_task_access_requires_assignment_or_company_planner(): void
    {
        $user = User::factory()->create();
        $project = Project::factory()->create(['company_id' => $user->company_id]);
        $own = Task::factory()->forProject($project)->create(['assigned_to' => $user->id]);
        $other = Task::factory()->forProject($project)->create();
        $foreign = Task::factory()->create();

        $this->actingAs($user, 'api');
        $this->getJson('/api/tasks/'.$own->id)->assertOk();
        $this->patchJson('/api/tasks/'.$own->id, ['progress' => 40])->assertOk();
        $this->getJson('/api/tasks/'.$other->id)->assertForbidden();
        $this->patchJson('/api/tasks/'.$other->id, ['progress' => 40])->assertForbidden();
        $this->getJson('/api/tasks/'.$foreign->id)->assertNotFound();
        $this->patchJson('/api/tasks/'.$foreign->id, ['progress' => 40])->assertNotFound();

        $user->forceFill(['role' => User::ROLE_MANAGER])->save();
        $this->getJson('/api/tasks/'.$other->id)->assertOk();
        $this->patchJson('/api/tasks/'.$other->id, ['progress' => 40])->assertOk();
    }

    public function test_inactive_web_session_is_logged_out(): void
    {
        $user = User::factory()->create(['status' => 'inactive']);
        $this->actingAs($user)->get('/dashboard')->assertRedirect(route('login'));
        $this->assertGuest('web');
    }

    public function test_disabled_login_errors_are_generic(): void
    {
        $user = User::factory()->create(['status' => 'inactive']);
        $credentials = ['email' => $user->email, 'password' => 'password'];

        $this->postJson('/api/login', $credentials)
            ->assertUnprocessable()->assertJsonPath('errors.email.0', __('auth.failed'));
        $this->post('/login', $credentials)
            ->assertSessionHasErrors(['email' => __('auth.failed')]);
        $this->assertGuest('web');
        $this->assertDatabaseCount('api_tokens', 0);
    }

    public function test_expired_revoked_and_unbounded_tokens_cannot_authenticate(): void
    {
        $user = User::factory()->create();
        foreach ([['expires_at' => now()->subSecond()], ['revoked_at' => now()], ['expires_at' => null]] as $attributes) {
            [$token, $plain] = ApiToken::issue($user, 'test');
            $token->forceFill($attributes)->save();
            $this->app['auth']->forgetGuards();
            $this->withToken($plain)->getJson('/api/me')->assertUnauthorized();
        }
    }

    public function test_valid_token_can_authenticate_and_logout_revokes_it(): void
    {
        $user = User::factory()->create();
        [$token, $plain] = ApiToken::issue($user, 'test');
        $this->withToken($plain)->getJson('/api/me')->assertOk();
        $this->app['auth']->forgetGuards();
        $this->withToken($plain)->postJson('/api/logout')->assertOk();
        $this->assertNotNull($token->fresh()->revoked_at);
        $this->app['auth']->forgetGuards();
        $this->withToken($plain)->getJson('/api/me')->assertUnauthorized();
    }

    public function test_password_changes_and_deactivation_revoke_all_tokens(): void
    {
        $user = User::factory()->create();
        [$first] = ApiToken::issue($user, 'first');
        [$second] = ApiToken::issue($user, 'second');
        $user->update(['password' => 'new-password']);
        $this->assertNotNull($first->fresh()->revoked_at);
        $this->assertNotNull($second->fresh()->revoked_at);

        [$third] = ApiToken::issue($user, 'third');
        $user->forceFill(['status' => 'inactive'])->save();
        $this->assertNotNull($third->fresh()->revoked_at);
        $user->forceFill(['status' => 'active'])->save();
        $this->assertNotNull($third->fresh()->revoked_at);
    }

    public function test_csv_export_neutralizes_formulas_and_preserves_numeric_values(): void
    {
        $method = new \ReflectionMethod(ReportController::class, 'streamCsv');
        $values = ['=1+1', '+SUM(A1)', '-1+2', '@SUM(A1)', '  =1+1', "\tvalue", "\rvalue", 'ordinary', -15.5, null];
        $response = $method->invoke(new ReportController(), 'test.csv', ['Values'], [$values]);
        ob_start();
        $response->sendContent();
        $csv = ob_get_clean();
        $stream = fopen('php://memory', 'r+');
        fwrite($stream, $csv);
        rewind($stream);
        fgetcsv($stream, escape: '');
        $row = fgetcsv($stream, escape: '');
        fclose($stream);

        foreach (array_slice($values, 0, 7) as $index => $value) {
            $this->assertSame("'".$value, $row[$index]);
        }
        $this->assertSame(['ordinary', '-15.5', ''], array_slice($row, 7));
    }
}
