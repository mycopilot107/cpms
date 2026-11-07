<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TaskPhotoResource;
use App\Models\Task;
use App\Models\TaskPhoto;
use App\Services\TaskPhotoStore;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Task photos for the mobile app. The same people who may open a task in the
 * app (its assignee and company planners) may see and add its photos.
 */
class TaskPhotoController extends Controller
{
    public function index(Task $task): AnonymousResourceCollection
    {
        $this->authorize('accessApi', $task);

        return TaskPhotoResource::collection($task->photos()->with('user')->get());
    }

    public function store(Request $request, Task $task, TaskPhotoStore $store): JsonResponse
    {
        $this->authorize('accessApi', $task);

        $validated = $request->validate([
            'photo' => ['required', 'file', 'image', 'mimes:jpeg,jpg,png,webp', 'max:15360'],
            'caption' => ['nullable', 'string', 'max:255'],
        ]);

        $photo = $store->store($request->file('photo'), $task, $request->user(), $validated['caption'] ?? null);

        return (new TaskPhotoResource($photo->load('user')))->response()->setStatusCode(201);
    }

    public function destroy(Request $request, TaskPhoto $photo): Response
    {
        abort_unless($photo->canBeDeletedBy($request->user()), 403);

        $photo->delete();

        return response()->noContent();
    }

    /** The image itself (?size=thumb for the small version). */
    public function file(Request $request, TaskPhoto $photo): StreamedResponse
    {
        $this->authorize('accessApi', $photo->task);

        return Storage::disk(TaskPhoto::DISK)->response(
            $request->query('size') === 'thumb' ? $photo->thumb_path : $photo->path,
            headers: ['Cache-Control' => 'private, max-age=86400'],
        );
    }
}
