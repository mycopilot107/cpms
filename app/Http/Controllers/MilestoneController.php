<?php

namespace App\Http\Controllers;

use App\Http\Requests\MilestoneRequest;
use App\Models\Milestone;
use App\Models\Project;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MilestoneController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Milestone::class);

        $projectId = $request->integer('project');
        $project = $projectId ? Project::find($projectId) : null;

        $milestones = Milestone::with('project')
            ->when($project, fn ($q) => $q->where('project_id', $project->id))
            ->orderByRaw('due_date is null, due_date')
            ->orderBy('sort_order')
            ->paginate(20)
            ->withQueryString();

        return view('planning.milestones.index', [
            'milestones' => $milestones,
            'projects' => Project::orderBy('name')->get(),
            'selectedProject' => $project,
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorize('create', Milestone::class);

        return view('planning.milestones.create', [
            'projects' => Project::orderBy('name')->get(),
            'statuses' => Milestone::STATUSES,
            'selectedProjectId' => $request->integer('project') ?: null,
        ]);
    }

    public function store(MilestoneRequest $request): RedirectResponse
    {
        $milestone = new Milestone($request->validated());
        $milestone->created_by = $request->user()->id;

        if ($milestone->status === Milestone::STATUS_COMPLETED) {
            $milestone->completed_at = now();
        }

        $milestone->save();

        return redirect()
            ->route('milestones.index', ['project' => $milestone->project_id])
            ->with('status', 'Milestone added.');
    }

    public function edit(Milestone $milestone): View
    {
        $this->authorize('update', $milestone);

        return view('planning.milestones.edit', [
            'milestone' => $milestone,
            'projects' => Project::orderBy('name')->get(),
            'statuses' => Milestone::STATUSES,
        ]);
    }

    public function update(MilestoneRequest $request, Milestone $milestone): RedirectResponse
    {
        $data = $request->validated();

        // Stamp/clear completed_at as the status crosses the "completed" line.
        if ($data['status'] === Milestone::STATUS_COMPLETED && ! $milestone->isComplete()) {
            $data['completed_at'] = now();
        } elseif ($data['status'] !== Milestone::STATUS_COMPLETED) {
            $data['completed_at'] = null;
        }

        $milestone->update($data);

        return redirect()
            ->route('milestones.index', ['project' => $milestone->project_id])
            ->with('status', 'Milestone updated.');
    }

    public function destroy(Milestone $milestone): RedirectResponse
    {
        $this->authorize('delete', $milestone);

        $projectId = $milestone->project_id;
        $milestone->delete();

        return redirect()
            ->route('milestones.index', ['project' => $projectId])
            ->with('status', 'Milestone deleted.');
    }
}
