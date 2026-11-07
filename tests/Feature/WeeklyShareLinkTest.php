<?php

namespace Tests\Feature;

use App\Models\DailyReport;
use App\Models\DailyReportPhoto;
use App\Models\Expense;
use App\Models\Milestone;
use App\Models\Project;
use App\Models\ProjectShareLink;
use App\Models\Role;
use App\Models\Task;
use App\Models\User;
use App\Services\PhotoStore;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class WeeklyShareLinkTest extends TestCase
{
    use RefreshDatabase;

    private User $manager;

    private Project $project;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake(PhotoStore::DISK);
        $this->travelTo(Carbon::parse('2026-11-05 15:00'));

        $this->manager = User::factory()->create(['role_id' => Role::idFor(User::ROLE_MANAGER), 'name' => 'Priya Manager']);
        $this->project = Project::factory()->forCompany($this->manager->company)->status(Project::STATUS_ACTIVE)->create(['name' => 'Skyline Tower']);
    }

    /** Back to a guest — the client has no login, on the web or the API guard. */
    private function signOut(): void
    {
        Auth::forgetGuards();
        Auth::shouldUse('web');
    }

    private function createLink(array $overrides = []): ProjectShareLink
    {
        $this->actingAs($this->manager)->post(route('projects.share.store', $this->project), array_merge([
            'week_start' => '2026-11-02',
            'message' => 'Slab done, brickwork next.',
            'include_photos' => 1,
            'include_labour' => 1,
            'include_notes' => 0,
            'expires' => '30',
        ], $overrides))->assertRedirect(route('projects.share', $this->project));

        $this->signOut();

        return ProjectShareLink::query()->withoutGlobalScopes()->latest('id')->firstOrFail();
    }

    public function test_client_opens_the_weekly_update_without_logging_in(): void
    {
        $done = Task::factory()->forProject($this->project)->create(['title' => 'Pour 3rd floor slab', 'status' => Task::STATUS_PENDING]);
        $done->update(['status' => Task::STATUS_COMPLETED, 'progress' => 100]);
        Task::factory()->forProject($this->project)->create(['title' => 'Brickwork ground floor', 'status' => Task::STATUS_IN_PROGRESS, 'progress' => 40, 'due_date' => '2026-11-20']);
        Task::factory()->forProject($this->project)->create(['title' => 'Plumbing sleeves', 'status' => Task::STATUS_PENDING, 'progress' => 0, 'due_date' => '2026-10-30']);
        $milestone = new Milestone(['project_id' => $this->project->id, 'name' => 'Structure up to 3rd floor', 'status' => Milestone::STATUS_IN_PROGRESS]);
        $milestone->forceFill(['company_id' => $this->project->company_id])->save();
        DailyReport::factory()->create([
            'project_id' => $this->project->id,
            'user_id' => $this->manager->id,
            'report_date' => '2026-11-04',
            'work_done' => 'Shuttering checked, concrete poured.',
            'labour_count' => 22,
            'labour_breakdown' => [['trade' => 'Mason', 'count' => 8], ['trade' => 'Helper', 'count' => 14]],
            'notes' => 'Internal: cement short.',
        ]);

        $link = $this->createLink();

        $this->assertGuest();
        $this->get($link->url())
            ->assertOk()
            ->assertHeader('X-Robots-Tag', 'noindex, nofollow')
            ->assertSee('Skyline Tower')
            ->assertSee('Weekly update · 2–8 Nov 2026')
            ->assertSee('Slab done, brickwork next.')
            ->assertSee('Pour 3rd floor slab')
            ->assertSee('Brickwork ground floor')
            ->assertSee('Plumbing sleeves')
            ->assertSee('Shuttering checked, concrete poured.')
            ->assertSee('22 labour')
            ->assertSee('Structure up to 3rd floor')
            ->assertDontSee('Internal: cement short.');

        $this->assertSame(1, $link->refresh()->view_count);
    }

    public function test_costs_are_never_shown(): void
    {
        $this->actingAs($this->manager);
        Expense::query()->create(['project_id' => $this->project->id, 'category' => 'material', 'description' => 'Secret cement invoice', 'amount' => 987654, 'expense_date' => '2026-11-03']);
        $link = $this->createLink();

        $this->get($link->url())->assertOk()->assertDontSee('Secret cement invoice')->assertDontSee('987,654');
    }

    public function test_notes_show_only_when_chosen(): void
    {
        DailyReport::factory()->create(['project_id' => $this->project->id, 'report_date' => '2026-11-03', 'notes' => 'Rain stopped work after 3 pm.']);

        $this->get($this->createLink(['include_notes' => 1])->url())->assertSee('Rain stopped work after 3 pm.');
    }

    public function test_week_photos_are_shown_and_served_but_other_weeks_are_not(): void
    {
        $thisWeek = DailyReport::factory()->create(['project_id' => $this->project->id, 'user_id' => $this->manager->id, 'report_date' => '2026-11-04']);
        $earlier = DailyReport::factory()->create(['project_id' => $this->project->id, 'user_id' => $this->manager->id, 'report_date' => '2026-10-20']);
        $this->actingAs($this->manager, 'api')->postJson("/api/daily-reports/{$thisWeek->id}/photos", ['photo' => UploadedFile::fake()->image('a.jpg')])->assertCreated();
        DailyReportPhoto::query()->withoutGlobalScopes()->update(['created_at' => now()]);
        $photo = DailyReportPhoto::query()->withoutGlobalScopes()->sole();

        $otherPhoto = new DailyReportPhoto;
        $otherPhoto->forceFill($photo->only(['company_id', 'path', 'thumb_path', 'size', 'width', 'height']) + ['daily_report_id' => $earlier->id])->save();

        $link = $this->createLink();

        $this->get($link->url())->assertSee(route('weekly-update.photo', ['token' => $link->token, 'kind' => 'daily', 'photo' => $photo->id, 'size' => 'thumb']), false);
        $this->get(route('weekly-update.photo', ['token' => $link->token, 'kind' => 'daily', 'photo' => $photo->id]))->assertOk();
        $this->get(route('weekly-update.photo', ['token' => $link->token, 'kind' => 'daily', 'photo' => $otherPhoto->id]))->assertNotFound();
    }

    public function test_photos_are_hidden_when_turned_off(): void
    {
        $report = DailyReport::factory()->create(['project_id' => $this->project->id, 'user_id' => $this->manager->id, 'report_date' => '2026-11-04']);
        $this->actingAs($this->manager, 'api')->postJson("/api/daily-reports/{$report->id}/photos", ['photo' => UploadedFile::fake()->image('a.jpg')]);
        $photo = DailyReportPhoto::query()->withoutGlobalScopes()->sole();

        $link = $this->createLink(['include_photos' => 0]);

        $this->get(route('weekly-update.photo', ['token' => $link->token, 'kind' => 'daily', 'photo' => $photo->id]))->assertNotFound();
    }

    public function test_turned_off_and_expired_links_stop_working(): void
    {
        $link = $this->createLink();

        $this->actingAs($this->manager)->delete(route('share-links.revoke', $link))->assertRedirect();
        $this->signOut();
        $this->get($link->url())->assertNotFound()->assertSee('This link is not available');

        $fresh = $this->createLink();
        $this->travel(31)->days();
        $this->get($fresh->url())->assertNotFound();
    }

    public function test_wrong_token_is_not_found(): void
    {
        $this->get('/share/'.str_repeat('a', 40))->assertNotFound();
    }

    public function test_share_page_shows_whatsapp_and_email_buttons(): void
    {
        $link = $this->createLink();

        $this->actingAs($this->manager)
            ->get(route('projects.share', $this->project))
            ->assertOk()
            ->assertSee('https://wa.me/?text=', false)
            ->assertSee('mailto:?subject=', false)
            ->assertSee($link->url(), false);
    }

    public function test_site_worker_cannot_create_links(): void
    {
        $worker = User::factory()->forCompany($this->manager->company)->create();

        $this->actingAs($worker)->get(route('projects.share', $this->project))->assertForbidden();
        $this->actingAs($worker)->post(route('projects.share.store', $this->project), ['week_start' => '2026-11-02', 'expires' => '30'])->assertForbidden();
    }

    public function test_other_company_cannot_share_or_revoke(): void
    {
        $link = $this->createLink();
        $outsider = User::factory()->create(['role_id' => Role::idFor(User::ROLE_MANAGER)]);

        $this->actingAs($outsider)->get(route('projects.share', $this->project))->assertNotFound();
        $this->actingAs($outsider)->delete(route('share-links.revoke', $link))->assertNotFound();
        $this->assertNull($link->refresh()->revoked_at);
    }

    public function test_completing_a_task_stamps_completed_at_and_reopening_clears_it(): void
    {
        $task = Task::factory()->forProject($this->project)->create(['status' => Task::STATUS_IN_PROGRESS]);

        $task->update(['status' => Task::STATUS_COMPLETED]);
        $this->assertSame('2026-11-05 15:00:00', $task->completed_at->toDateTimeString());

        $task->update(['status' => Task::STATUS_IN_PROGRESS]);
        $this->assertNull($task->completed_at);
    }
}
