<?php

namespace Tests\Feature;

use App\Models\DailyReport;
use App\Models\DailyReportPhoto;
use App\Models\Project;
use App\Models\Role;
use App\Models\User;
use App\Services\PhotoStore;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DailyReportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake(PhotoStore::DISK);
    }

    /**
     * @return array{0: User, 1: Project}
     */
    private function engineerWithProject(): array
    {
        $engineer = User::factory()->create(['role_id' => Role::idFor(User::ROLE_ENGINEER)]);
        $project = Project::factory()->forCompany($engineer->company)->status(Project::STATUS_ACTIVE)->create(['name' => 'Skyline Tower']);

        return [$engineer, $project];
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(Project $project, array $overrides = []): array
    {
        return array_merge([
            'project_id' => $project->id,
            'report_date' => today()->toDateString(),
            'work_done' => 'Slab shuttering on 3rd floor finished. Steel binding started.',
            'labour' => [
                ['trade' => 'Mason', 'count' => 6],
                ['trade' => 'Helper', 'count' => 10],
                ['trade' => 'Bar bender', 'count' => 4],
            ],
            'notes' => 'Cement stock for 2 days only.',
        ], $overrides);
    }

    public function test_engineer_files_todays_report_from_the_app(): void
    {
        [$engineer, $project] = $this->engineerWithProject();

        $this->actingAs($engineer, 'api')
            ->postJson('/api/daily-reports', $this->payload($project))
            ->assertCreated()
            ->assertJsonPath('data.labour_count', 20)
            ->assertJsonPath('data.project.name', 'Skyline Tower')
            ->assertJsonPath('data.editable', true);

        $report = DailyReport::query()->sole();
        $this->assertSame($engineer->id, $report->user_id);
        $this->assertSame($engineer->company_id, $report->company_id);
        $this->assertSame('Cement stock for 2 days only.', $report->notes);
        $this->assertSame([['trade' => 'Mason', 'count' => 6], ['trade' => 'Helper', 'count' => 10], ['trade' => 'Bar bender', 'count' => 4]], $report->labour_breakdown);
    }

    public function test_saving_again_the_same_day_updates_the_report(): void
    {
        [$engineer, $project] = $this->engineerWithProject();
        $this->actingAs($engineer, 'api')->postJson('/api/daily-reports', $this->payload($project))->assertCreated();

        $this->actingAs($engineer, 'api')
            ->postJson('/api/daily-reports', $this->payload($project, ['labour' => null, 'labour_count' => 25, 'work_done' => 'Slab poured.']))
            ->assertOk()
            ->assertJsonPath('data.labour_count', 25);

        $report = DailyReport::query()->sole();
        $this->assertSame('Slab poured.', $report->work_done);
        $this->assertNull($report->labour_breakdown);
    }

    public function test_future_dates_and_old_dates_are_rejected(): void
    {
        [$engineer, $project] = $this->engineerWithProject();

        $this->actingAs($engineer, 'api')
            ->postJson('/api/daily-reports', $this->payload($project, ['report_date' => today()->addDay()->toDateString()]))
            ->assertJsonValidationErrors(['report_date' => 'A report cannot be for a future date.']);

        $this->actingAs($engineer, 'api')
            ->postJson('/api/daily-reports', $this->payload($project, ['report_date' => today()->subDays(10)->toDateString()]))
            ->assertJsonValidationErrors('report_date');
    }

    public function test_work_done_and_labour_are_required(): void
    {
        [$engineer, $project] = $this->engineerWithProject();

        $this->actingAs($engineer, 'api')
            ->postJson('/api/daily-reports', ['project_id' => $project->id, 'report_date' => today()->toDateString()])
            ->assertJsonValidationErrors(['work_done', 'labour_count']);
    }

    public function test_project_of_another_company_is_rejected(): void
    {
        [$engineer] = $this->engineerWithProject();
        $foreign = Project::factory()->status(Project::STATUS_ACTIVE)->create();

        $this->actingAs($engineer, 'api')
            ->postJson('/api/daily-reports', $this->payload($foreign))
            ->assertJsonValidationErrors('project_id');
    }

    public function test_photo_is_added_to_the_report(): void
    {
        [$engineer, $project] = $this->engineerWithProject();
        $reportId = $this->actingAs($engineer, 'api')->postJson('/api/daily-reports', $this->payload($project))->json('data.id');

        $this->actingAs($engineer, 'api')
            ->postJson("/api/daily-reports/{$reportId}/photos", ['photo' => UploadedFile::fake()->image('slab.jpg', 3000, 2000)])
            ->assertCreated();

        $photo = DailyReportPhoto::query()->sole();
        Storage::disk(PhotoStore::DISK)->assertExists([$photo->path, $photo->thumb_path]);

        $this->actingAs($engineer, 'api')
            ->getJson('/api/daily-reports?date='.today()->toDateString())
            ->assertJsonCount(1, 'data')
            ->assertJsonCount(1, 'data.0.photos');
    }

    public function test_someone_else_cannot_add_photos_to_my_report(): void
    {
        [$engineer, $project] = $this->engineerWithProject();
        $reportId = $this->actingAs($engineer, 'api')->postJson('/api/daily-reports', $this->payload($project))->json('data.id');
        $colleague = User::factory()->forCompany($engineer->company)->create();

        $this->actingAs($colleague, 'api')
            ->postJson("/api/daily-reports/{$reportId}/photos", ['photo' => UploadedFile::fake()->image('x.jpg')])
            ->assertForbidden();
    }

    public function test_office_reads_every_report_and_site_staff_see_only_their_own(): void
    {
        [$engineer, $project] = $this->engineerWithProject();
        $this->actingAs($engineer, 'api')->postJson('/api/daily-reports', $this->payload($project));
        $supervisor = User::factory()->forCompany($engineer->company)->create(['role_id' => Role::idFor(User::ROLE_SUPERVISOR)]);
        DailyReport::factory()->create(['project_id' => $project->id, 'user_id' => $supervisor->id, 'work_done' => 'Supervisor walk-through']);
        $manager = User::factory()->forCompany($engineer->company)->create(['role_id' => Role::idFor(User::ROLE_MANAGER)]);

        $this->actingAs($manager)
            ->get(route('daily-reports.index'))
            ->assertOk()
            ->assertSee('Slab shuttering on 3rd floor finished.')
            ->assertSee('Supervisor walk-through')
            ->assertSee('Cement stock for 2 days only.');

        $this->actingAs($engineer)
            ->get(route('daily-reports.index'))
            ->assertOk()
            ->assertSee('Slab shuttering on 3rd floor finished.')
            ->assertDontSee('Supervisor walk-through');

        $supervisorReport = DailyReport::query()->where('user_id', $supervisor->id)->sole();
        $this->actingAs($engineer)->get(route('daily-reports.show', $supervisorReport))->assertForbidden();
    }

    public function test_report_page_shows_photos_and_labour(): void
    {
        [$engineer, $project] = $this->engineerWithProject();
        $reportId = $this->actingAs($engineer, 'api')->postJson('/api/daily-reports', $this->payload($project))->json('data.id');
        $this->actingAs($engineer, 'api')->postJson("/api/daily-reports/{$reportId}/photos", ['photo' => UploadedFile::fake()->image('a.jpg')]);
        $photo = DailyReportPhoto::query()->sole();
        $manager = User::factory()->forCompany($engineer->company)->create(['role_id' => Role::idFor(User::ROLE_MANAGER)]);

        $this->actingAs($manager)
            ->get(route('daily-reports.show', $reportId))
            ->assertOk()
            ->assertSee('Bar bender · 4')
            ->assertSee(route('daily-report-photos.file', ['photo' => $photo, 'size' => 'thumb']), false);

        $this->actingAs($manager)->get(route('daily-report-photos.file', $photo))->assertOk()->assertHeader('content-type', 'image/jpeg');
    }

    public function test_project_page_lists_recent_reports(): void
    {
        [$engineer, $project] = $this->engineerWithProject();
        $this->actingAs($engineer, 'api')->postJson('/api/daily-reports', $this->payload($project));
        $manager = User::factory()->forCompany($engineer->company)->create(['role_id' => Role::idFor(User::ROLE_MANAGER)]);

        $this->actingAs($manager)
            ->get(route('projects.show', $project))
            ->assertOk()
            ->assertSee('Daily site reports')
            ->assertSee('20 labour');
    }

    public function test_reports_older_than_two_weeks_stay_on_the_list(): void
    {
        [$engineer, $project] = $this->engineerWithProject();
        DailyReport::factory()->create([
            'project_id' => $project->id,
            'user_id' => $engineer->id,
            'report_date' => today()->subDays(40),
            'work_done' => 'Foundation stone laid.',
        ]);
        $manager = User::factory()->forCompany($engineer->company)->create(['role_id' => Role::idFor(User::ROLE_MANAGER)]);

        $this->actingAs($manager)
            ->get(route('daily-reports.index'))
            ->assertOk()
            ->assertSee('Foundation stone laid.');

        $this->actingAs($manager)
            ->get(route('daily-reports.index', [
                'from' => today()->subDays(7)->toDateString(),
                'to' => today()->toDateString(),
            ]))
            ->assertOk()
            ->assertDontSee('Foundation stone laid.');
    }

    public function test_reports_export_to_csv(): void
    {
        [$engineer, $project] = $this->engineerWithProject();
        $this->actingAs($engineer, 'api')->postJson('/api/daily-reports', $this->payload($project));
        $manager = User::factory()->forCompany($engineer->company)->create(['role_id' => Role::idFor(User::ROLE_MANAGER)]);

        $response = $this->actingAs($manager)->get(route('daily-reports.index', ['export' => 'csv']));

        $response->assertOk();
        $this->assertStringContainsString('Mason: 6, Helper: 10, Bar bender: 4', $response->streamedContent());
    }

    public function test_app_gets_open_projects_and_trades(): void
    {
        [$engineer, $project] = $this->engineerWithProject();
        Project::factory()->forCompany($engineer->company)->status(Project::STATUS_COMPLETED)->create(['name' => 'Finished Villa']);

        $this->actingAs($engineer, 'api')
            ->getJson('/api/daily-reports/projects')
            ->assertOk()
            ->assertJsonPath('data.0.name', 'Skyline Tower')
            ->assertJsonCount(1, 'data')
            ->assertJsonFragment(['Mason']);
    }
}
