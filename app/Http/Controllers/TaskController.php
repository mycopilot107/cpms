<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TaskController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Task::class);

        $query = Task::with(['project', 'assignee']);

        // Optional filters. project_id/status are validated against the tenant's
        // own data (the global scope already prevents cross-tenant leakage).
        if ($request->filled('project')) {
            $query->where('project_id', $request->integer('project'));
        }

        if ($request->filled('status') && in_array($request->input('status'), Task::STATUSES, true)) {
            $query->where('status', $request->input('status'));
        }

        if ($request->boolean('mine')) {
            $query->where('assigned_to', $request->user()->id);
        }

        $tasks = $query->latest()->paginate(15)->withQueryString();

        return view('tasks.index', [
            'tasks' => $tasks,
            'projects' => Project::orderBy('name')->get(),
            'statuses' => Task::STATUSES,
            'filters' => [
                'project' => $request->input('project'),
                'status' => $request->input('status'),
                'mine' => $request->boolean('mine'),
            ],
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorize('create', Task::class);

        return view('tasks.create', $this->formData($request));
    }

    public function store(StoreTaskRequest $request): RedirectResponse
    {
        $task = new Task($request->validated());
        // company_id via trait; created_by from the authenticated user.
        $task->created_by = $request->user()->id;
        $task->save();

        return redirect()
            ->route('tasks.show', $task)
            ->with('status', 'Task created.');
    }

    public function show(Task $task): View
    {
        $this->authorize('view', $task);

        $task->load(['project', 'assignee', 'creator', 'dependency']);

        return view('tasks.show', ['task' => $task]);
    }

    public function edit(Request $request, Task $task): View
    {
        $this->authorize('update', $task);

        return view('tasks.edit', $this->formData($request, $task) + ['task' => $task]);
    }

    public function update(UpdateTaskRequest $request, Task $task): RedirectResponse
    {
        $task->update($request->validated());

        return redirect()
            ->route('tasks.show', $task)
            ->with('status', 'Task updated.');
    }

    public function destroy(Task $task): RedirectResponse
    {
        $this->authorize('delete', $task);

        $task->delete();

        return redirect()
            ->route('tasks.index')
            ->with('status', 'Task deleted.');
    }

    /**
     * Shared select-list data for the create/edit forms.
     *
     * @return array<string, mixed>
     */
    private function formData(Request $request, ?Task $task = null): array
    {
        return [
            'projects' => Project::orderBy('name')->get(),
            'assignees' => User::where('company_id', $request->user()->company_id)
                ->where('status', 'active')
                ->orderBy('name')
                ->get(),
            // Candidate dependencies: other tasks in the tenant (exclude self).
            'dependencies' => Task::when($task, fn ($q) => $q->whereKeyNot($task->getKey()))
                ->orderBy('title')
                ->get(),
            'statuses' => Task::STATUSES,
            'priorities' => Task::PRIORITIES,
        ];
    }
}
