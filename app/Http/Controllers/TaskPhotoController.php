<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\TaskPhoto;
use App\Services\TaskPhotoStore;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Task photos on the website. Anyone in the company who can open the task
 * sees its photos; people who can update the task can add more.
 */
class TaskPhotoController extends Controller
{
    public function store(Request $request, Task $task, TaskPhotoStore $store): RedirectResponse
    {
        $this->authorize('update', $task);

        $validated = $request->validate([
            'photos' => ['required', 'array', 'max:10'],
            'photos.*' => ['file', 'image', 'mimes:jpeg,jpg,png,webp', 'max:15360'],
            'caption' => ['nullable', 'string', 'max:255'],
        ], [
            'photos.required' => 'Choose at least one photo.',
            'photos.max' => 'Add up to 10 photos at a time.',
            'photos.*.image' => 'Each file must be a JPG, PNG, or WebP photo.',
            'photos.*.mimes' => 'Each file must be a JPG, PNG, or WebP photo.',
            'photos.*.max' => 'Each photo must be 15 MB or smaller.',
        ]);

        foreach ($request->file('photos') as $file) {
            $store->store($file, $task, $request->user(), $validated['caption'] ?? null);
        }

        $count = count($request->file('photos'));

        return redirect()
            ->to(route('tasks.show', $task).'#photos')
            ->with('status', $count === 1 ? 'Photo added.' : "{$count} photos added.");
    }

    public function destroy(Request $request, TaskPhoto $photo): RedirectResponse
    {
        abort_unless($photo->canBeDeletedBy($request->user()), 403);

        $task = $photo->task;
        $photo->delete();

        return redirect()
            ->to(route('tasks.show', $task).'#photos')
            ->with('status', 'Photo deleted.');
    }

    public function file(Request $request, TaskPhoto $photo): StreamedResponse
    {
        $this->authorize('view', $photo->task);

        return Storage::disk(TaskPhoto::DISK)->response(
            $request->query('size') === 'thumb' ? $photo->thumb_path : $photo->path,
            headers: ['Cache-Control' => 'private, max-age=86400'],
        );
    }
}
