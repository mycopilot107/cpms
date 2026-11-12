<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\Role;
use App\Models\User;
use App\Services\ProjectSheetParser;
use App\Support\SpreadsheetReader;
use App\Support\SpreadsheetWriter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProjectImportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
    }

    public function test_sample_workbook_downloads_and_contains_the_example_projects(): void
    {
        $manager = $this->manager();

        $response = $this->actingAs($manager)->get(route('projects.import.template'));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

        $path = tempnam(sys_get_temp_dir(), 'projects').'.xlsx';
        file_put_contents($path, $response->getContent());

        $rows = app(SpreadsheetReader::class)->rows($path, 'xlsx');
        unlink($path);

        $this->assertSame(ProjectSheetParser::SAMPLE_ROWS, $rows);
    }

    public function test_manager_imports_the_ready_rows_and_skips_a_bad_one(): void
    {
        $manager = $this->manager();
        Project::factory()->forCompany($manager->company)->create(['project_code' => 'PRJ-TAKEN']);

        $file = $this->workbook([
            ProjectSheetParser::SAMPLE_ROWS[0],
            ['Riverside Residency', 'PRJ-1001', 'Sharma Builders', '9876543210', 'Plot 12, Indore', '01/04/2026', '31/03/2027', '2,50,00,000', 'On hold', 'Tower A'],
            ['', 'PRJ-9', '', '', '', '', '', '', '', ''],
            ['Taken code', 'PRJ-TAKEN', 'Someone', '', '', '2026-01-01', '2025-01-01', '-5', 'nope', ''],
            ['Metro Bridge', 'PRJ-1001', '', '', '', '', '', '', '', ''],
        ]);

        $preview = $this->actingAs($manager)->post(route('projects.import.store'), ['file' => $file]);

        $preview->assertRedirect();
        $location = (string) $preview->headers->get('Location');
        $this->assertMatchesRegularExpression('#/projects/import/([A-Za-z0-9]{32})$#', $location);

        $this->actingAs($manager)->get($location)
            ->assertOk()
            ->assertSee('Riverside Residency')
            ->assertSee('Project name is required.')
            ->assertSee('already used')
            ->assertSee('before the start date')
            ->assertSee('used more than once');

        $this->actingAs($manager)->post($location)->assertRedirect(route('projects.index'));

        $this->assertSame(1, Project::query()->where('company_id', $manager->company_id)->where('project_code', 'PRJ-1001')->count());
        $created = Project::query()->where('project_code', 'PRJ-1001')->first();
        $this->assertNotNull($created);
        $this->assertSame('Riverside Residency', $created->name);
        $this->assertSame(Project::STATUS_ON_HOLD, $created->status);
        $this->assertSame('2026-04-01', $created->start_date->toDateString());
        $this->assertSame('2027-03-31', $created->end_date->toDateString());
        $this->assertSame('25000000.00', $created->budget);
        $this->assertSame($manager->id, $created->created_by);
        $this->assertSame($manager->company_id, $created->company_id);
    }

    public function test_the_same_project_code_is_allowed_in_another_company(): void
    {
        $manager = $this->manager();
        Project::factory()->create(['project_code' => 'PRJ-1001']);

        $file = $this->workbook([
            ['Project name', 'Project code'],
            ['Own site', 'PRJ-1001'],
        ]);

        $preview = $this->actingAs($manager)->post(route('projects.import.store'), ['file' => $file]);
        $this->actingAs($manager)->post($preview->headers->get('Location'));

        $this->assertTrue(Project::query()->where('company_id', $manager->company_id)->where('project_code', 'PRJ-1001')->exists());
    }

    public function test_a_worker_cannot_import_projects(): void
    {
        $worker = User::factory()->create(['role_id' => Role::idFor(User::ROLE_WORKER)]);

        $this->actingAs($worker)->get(route('projects.import.create'))->assertForbidden();
        $this->actingAs($worker)->get(route('projects.import.template'))->assertForbidden();
        $this->actingAs($worker)->post(route('projects.import.store'), [
            'file' => UploadedFile::fake()->create('projects.csv', 1, 'text/csv'),
        ])->assertForbidden();
    }

    public function test_a_file_without_a_project_name_column_is_rejected(): void
    {
        $manager = $this->manager();

        $this->actingAs($manager)
            ->from(route('projects.import.create'))
            ->post(route('projects.import.store'), [
                'file' => $this->workbook([['Notes'], ['hello']]),
            ])
            ->assertRedirect(route('projects.import.create'))
            ->assertSessionHasErrors('file');

        $this->assertSame(0, Project::query()->count());
    }

    private function manager(): User
    {
        return User::factory()->create(['role_id' => Role::idFor(User::ROLE_MANAGER)]);
    }

    /**
     * @param  list<list<string>>  $rows
     */
    private function workbook(array $rows): UploadedFile
    {
        $bytes = app(SpreadsheetWriter::class)->xlsx($rows, 'Projects');

        return UploadedFile::fake()->createWithContent('projects.xlsx', $bytes);
    }
}
