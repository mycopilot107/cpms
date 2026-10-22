<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProjectPaymentRequest;
use App\Models\Project;
use App\Models\ProjectPayment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProjectPaymentController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', ProjectPayment::class);

        $projectId = $request->integer('project');
        $project = $projectId ? Project::find($projectId) : null;

        $direction = $request->string('direction')->toString();
        $direction = in_array($direction, ProjectPayment::DIRECTIONS, true) ? $direction : null;

        $payments = ProjectPayment::with('project')
            ->when($project, fn ($q) => $q->where('project_id', $project->id))
            ->when($direction, fn ($q) => $q->where('direction', $direction))
            ->orderByDesc('payment_date')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return view('finance.payments.index', [
            'payments' => $payments,
            'projects' => Project::orderBy('name')->get(),
            'selectedProject' => $project,
            'selectedDirection' => $direction,
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorize('create', ProjectPayment::class);

        return view('finance.payments.create', [
            'projects' => Project::orderBy('name')->get(),
            'directions' => ProjectPayment::DIRECTIONS,
            'methods' => ProjectPayment::METHODS,
            'selectedProjectId' => $request->integer('project') ?: null,
        ]);
    }

    public function store(ProjectPaymentRequest $request): RedirectResponse
    {
        $payment = new ProjectPayment($request->validated());
        $payment->created_by = $request->user()->id;
        $payment->save();

        return redirect()
            ->route('project-payments.index', ['project' => $payment->project_id])
            ->with('status', 'Payment recorded.');
    }

    public function edit(ProjectPayment $projectPayment): View
    {
        $this->authorize('update', $projectPayment);

        return view('finance.payments.edit', [
            'payment' => $projectPayment,
            'projects' => Project::orderBy('name')->get(),
            'directions' => ProjectPayment::DIRECTIONS,
            'methods' => ProjectPayment::METHODS,
        ]);
    }

    public function update(ProjectPaymentRequest $request, ProjectPayment $projectPayment): RedirectResponse
    {
        $projectPayment->update($request->validated());

        return redirect()
            ->route('project-payments.index', ['project' => $projectPayment->project_id])
            ->with('status', 'Payment updated.');
    }

    public function destroy(ProjectPayment $projectPayment): RedirectResponse
    {
        $this->authorize('delete', $projectPayment);

        $projectId = $projectPayment->project_id;
        $projectPayment->delete();

        return redirect()
            ->route('project-payments.index', ['project' => $projectId])
            ->with('status', 'Payment deleted.');
    }
}
