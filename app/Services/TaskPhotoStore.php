<?php

namespace App\Services;

use App\Models\Task;
use App\Models\TaskPhoto;
use App\Models\User;
use Illuminate\Http\UploadedFile;

/** Saves a photo on a task (from the Android app or the website). */
class TaskPhotoStore
{
    public function __construct(private readonly PhotoStore $photos) {}

    public function store(UploadedFile $file, Task $task, User $user, ?string $caption = null): TaskPhoto
    {
        $saved = $this->photos->save($file, 'task-photos/'.$task->company_id.'/'.$task->id);

        $photo = new TaskPhoto(['caption' => $caption !== null && trim($caption) !== '' ? trim($caption) : null]);
        $photo->forceFill($saved + [
            'company_id' => $task->company_id,
            'task_id' => $task->id,
            'user_id' => $user->id,
        ])->save();

        return $photo;
    }
}
