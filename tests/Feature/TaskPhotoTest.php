<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\Role;
use App\Models\Task;
use App\Models\TaskPhoto;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TaskPhotoTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake(TaskPhoto::DISK);
    }

    /**
     * @return array{0: User, 1: Task}
     */
    private function workerWithTask(): array
    {
        $worker = User::factory()->create();
        $project = Project::factory()->create(['company_id' => $worker->company_id]);
        $task = Task::factory()->forProject($project)->create(['assigned_to' => $worker->id]);

        return [$worker, $task];
    }

    public function test_assignee_uploads_a_photo_from_the_app(): void
    {
        [$worker, $task] = $this->workerWithTask();

        $response = $this->actingAs($worker, 'api')
            ->postJson("/api/tasks/{$task->id}/photos", [
                'photo' => UploadedFile::fake()->image('site.jpg', 4000, 3000),
                'caption' => 'Column C-4 shuttering done',
            ]);

        $response->assertCreated()
            ->assertJsonPath('data.caption', 'Column C-4 shuttering done')
            ->assertJsonPath('data.uploaded_by', $worker->name)
            ->assertJsonPath('data.can_delete', true);

        $photo = TaskPhoto::query()->sole();
        $this->assertSame($task->id, $photo->task_id);
        $this->assertSame(1920, $photo->width);
        $this->assertSame(1440, $photo->height);
        Storage::disk(TaskPhoto::DISK)->assertExists([$photo->path, $photo->thumb_path]);
    }

    public function test_app_lists_task_photos_and_serves_the_image(): void
    {
        [$worker, $task] = $this->workerWithTask();
        $this->actingAs($worker, 'api')->postJson("/api/tasks/{$task->id}/photos", ['photo' => UploadedFile::fake()->image('a.jpg')]);
        $photo = TaskPhoto::query()->sole();

        $this->actingAs($worker, 'api')
            ->getJson("/api/tasks/{$task->id}/photos")
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.thumb_url', route('api.task-photos.file', ['photo' => $photo->id, 'size' => 'thumb']));

        $this->actingAs($worker, 'api')
            ->get("/api/photos/{$photo->id}/file?size=thumb")
            ->assertOk()
            ->assertHeader('content-type', 'image/jpeg');
    }

    public function test_worker_cannot_add_photos_to_someone_elses_task(): void
    {
        [$worker, $task] = $this->workerWithTask();
        $otherTask = Task::factory()->forProject($task->project)->create();

        $this->actingAs($worker, 'api')
            ->postJson("/api/tasks/{$otherTask->id}/photos", ['photo' => UploadedFile::fake()->image('a.jpg')])
            ->assertForbidden();
    }

    public function test_photos_of_another_company_are_not_found(): void
    {
        [$worker, $task] = $this->workerWithTask();
        $this->actingAs($worker, 'api')->postJson("/api/tasks/{$task->id}/photos", ['photo' => UploadedFile::fake()->image('a.jpg')]);
        $photo = TaskPhoto::query()->sole();

        $outsider = User::factory()->create(['role_id' => Role::idFor(User::ROLE_MANAGER)]);

        $this->actingAs($outsider, 'api')->get("/api/photos/{$photo->id}/file")->assertNotFound();
        $this->actingAs($outsider)->get(route('task-photos.file', $photo->id))->assertNotFound();
    }

    public function test_non_image_upload_is_rejected(): void
    {
        [$worker, $task] = $this->workerWithTask();

        $this->actingAs($worker, 'api')
            ->postJson("/api/tasks/{$task->id}/photos", ['photo' => UploadedFile::fake()->create('notes.pdf', 50, 'application/pdf')])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('photo');

        $this->assertSame(0, TaskPhoto::query()->withoutGlobalScopes()->count());
    }

    public function test_office_sees_app_photos_on_the_task_page(): void
    {
        [$worker, $task] = $this->workerWithTask();
        $this->actingAs($worker, 'api')->postJson("/api/tasks/{$task->id}/photos", [
            'photo' => UploadedFile::fake()->image('a.jpg'),
            'caption' => 'Slab poured',
        ]);
        $manager = User::factory()->forCompany($task->company)->create(['role_id' => Role::idFor(User::ROLE_MANAGER)]);
        $photo = TaskPhoto::query()->sole();

        $this->actingAs($manager)
            ->get(route('tasks.show', $task))
            ->assertOk()
            ->assertSee('Site photos')
            ->assertSee('Slab poured')
            ->assertSee(route('task-photos.file', ['photo' => $photo, 'size' => 'thumb']), false);

        $this->actingAs($manager)
            ->get(route('task-photos.file', $photo))
            ->assertOk()
            ->assertHeader('content-type', 'image/jpeg');
    }

    public function test_office_can_upload_several_photos_from_the_web(): void
    {
        [, $task] = $this->workerWithTask();
        $manager = User::factory()->forCompany($task->company)->create(['role_id' => Role::idFor(User::ROLE_MANAGER)]);

        $this->actingAs($manager)
            ->post(route('tasks.photos.store', $task), [
                'photos' => [UploadedFile::fake()->image('one.png'), UploadedFile::fake()->image('two.jpg')],
            ])
            ->assertRedirect(route('tasks.show', $task).'#photos');

        $this->assertSame(2, $task->photos()->count());
    }

    public function test_uploader_or_planner_can_delete_but_other_workers_cannot(): void
    {
        [$worker, $task] = $this->workerWithTask();
        $this->actingAs($worker, 'api')->postJson("/api/tasks/{$task->id}/photos", ['photo' => UploadedFile::fake()->image('a.jpg')]);
        $photo = TaskPhoto::query()->sole();
        $otherWorker = User::factory()->forCompany($task->company)->create();

        $this->actingAs($otherWorker, 'api')->deleteJson("/api/photos/{$photo->id}")->assertForbidden();

        $this->actingAs($worker, 'api')->deleteJson("/api/photos/{$photo->id}")->assertNoContent();

        $this->assertModelMissing($photo);
        Storage::disk(TaskPhoto::DISK)->assertMissing([$photo->path, $photo->thumb_path]);
    }
}
