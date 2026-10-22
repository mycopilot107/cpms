<?php

namespace App\Http\Controllers;

use App\Http\Requests\BoqItemRequest;
use App\Models\BoqItem;
use App\Models\Project;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BoqItemController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', BoqItem::class);

        // Optional ?project= filter. The id is validated against the tenant's
        // own projects (the query is auto-scoped), so a foreign id yields null.
        $projectId = $request->integer('project');
        $project = $projectId ? Project::find($projectId) : null;

        $items = BoqItem::with('project')
            ->when($project, fn ($q) => $q->where('project_id', $project->id))
            ->orderBy('project_id')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->paginate(20)
            ->withQueryString();

        return view('finance.boq.index', [
            'items' => $items,
            'projects' => Project::orderBy('name')->get(),
            'selectedProject' => $project,
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorize('create', BoqItem::class);

        return view('finance.boq.create', [
            'projects' => Project::orderBy('name')->get(),
            'selectedProjectId' => $request->integer('project') ?: null,
        ]);
    }

    public function store(BoqItemRequest $request): RedirectResponse
    {
        $item = new BoqItem($request->validated());
        // Amount is authoritative on the server: quantity * rate, never trusted
        // from the browser.
        $item->amount = round((float) $request->validated()['quantity'] * (float) $request->validated()['rate'], 2);
        $item->created_by = $request->user()->id;
        $item->save();

        return redirect()
            ->route('boq-items.index', ['project' => $item->project_id])
            ->with('status', 'BOQ item added.');
    }

    public function edit(BoqItem $boqItem): View
    {
        $this->authorize('update', $boqItem);

        return view('finance.boq.edit', [
            'item' => $boqItem,
            'projects' => Project::orderBy('name')->get(),
        ]);
    }

    public function update(BoqItemRequest $request, BoqItem $boqItem): RedirectResponse
    {
        $data = $request->validated();
        $data['amount'] = round((float) $data['quantity'] * (float) $data['rate'], 2);
        $boqItem->update($data);

        return redirect()
            ->route('boq-items.index', ['project' => $boqItem->project_id])
            ->with('status', 'BOQ item updated.');
    }

    public function destroy(BoqItem $boqItem): RedirectResponse
    {
        $this->authorize('delete', $boqItem);

        $projectId = $boqItem->project_id;
        $boqItem->delete();

        return redirect()
            ->route('boq-items.index', ['project' => $projectId])
            ->with('status', 'BOQ item deleted.');
    }
}
