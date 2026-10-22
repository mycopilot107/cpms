<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\UpdateTaskApiRequest;
use App\Http\Resources\TaskResource;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * Task endpoints for the mobile app.
 *
 * Every query below is already constrained to the caller's company by the
 * BelongsToCompany global scope (active because the api guard authenticated a
 * user). On top of that, the listing only ever returns tasks assigned to the
 * caller, and route-model binding 404s any task id outside the tenant.
 */
class TaskController extends Controller
{
    /**
     * GET /api/tasks?scope=open|all — the caller's own assigned tasks.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Task::class);

        $scope = $request->query('scope', 'open');

        $tasks = Task::with(['project', 'assignee'])
            ->where('assigned_to', $request->user()->id)
            ->when($scope !== 'all', fn ($q) => $q->whereNotIn('status', [
                Task::STATUS_COMPLETED,
                Task::STATUS_CANCELLED,
            ]))
            // Soonest due first, undated tasks last, newest as tiebreak.
            ->orderByRaw('due_date IS NULL, due_date ASC')
            ->latest()
            ->get();

        return TaskResource::collection($tasks);
    }

    /**
     * GET /api/tasks/{task} — an assigned task (or company planner access).
     */
    public function show(Task $task): TaskResource
    {
        $this->authorize('accessApi', $task);

        $task->load(['project', 'assignee']);

        return new TaskResource($task);
    }

    /**
     * PATCH /api/tasks/{task} — report progress (status / percent / hours).
     */
    public function update(UpdateTaskApiRequest $request, Task $task): TaskResource
    {
        $task->update($request->validated());

        $task->load(['project', 'assignee']);

        return new TaskResource($task);
    }
}
