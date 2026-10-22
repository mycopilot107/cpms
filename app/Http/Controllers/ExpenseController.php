<?php

namespace App\Http\Controllers;

use App\Http\Requests\ExpenseRequest;
use App\Models\Expense;
use App\Models\Project;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ExpenseController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Expense::class);

        $projectId = $request->integer('project');
        $project = $projectId ? Project::find($projectId) : null;

        $category = $request->string('category')->toString();
        $category = in_array($category, Expense::CATEGORIES, true) ? $category : null;

        $expenses = Expense::with('project')
            ->when($project, fn ($q) => $q->where('project_id', $project->id))
            ->when($category, fn ($q) => $q->where('category', $category))
            ->orderByDesc('expense_date')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return view('finance.expenses.index', [
            'expenses' => $expenses,
            'projects' => Project::orderBy('name')->get(),
            'categories' => Expense::CATEGORIES,
            'selectedProject' => $project,
            'selectedCategory' => $category,
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorize('create', Expense::class);

        return view('finance.expenses.create', [
            'projects' => Project::orderBy('name')->get(),
            'categories' => Expense::CATEGORIES,
            'selectedProjectId' => $request->integer('project') ?: null,
        ]);
    }

    public function store(ExpenseRequest $request): RedirectResponse
    {
        $expense = new Expense($request->validated());
        $expense->created_by = $request->user()->id;
        $expense->save();

        return redirect()
            ->route('expenses.index', ['project' => $expense->project_id])
            ->with('status', 'Expense recorded.');
    }

    public function edit(Expense $expense): View
    {
        $this->authorize('update', $expense);

        return view('finance.expenses.edit', [
            'expense' => $expense,
            'projects' => Project::orderBy('name')->get(),
            'categories' => Expense::CATEGORIES,
        ]);
    }

    public function update(ExpenseRequest $request, Expense $expense): RedirectResponse
    {
        $expense->update($request->validated());

        return redirect()
            ->route('expenses.index', ['project' => $expense->project_id])
            ->with('status', 'Expense updated.');
    }

    public function destroy(Expense $expense): RedirectResponse
    {
        $this->authorize('delete', $expense);

        $projectId = $expense->project_id;
        $expense->delete();

        return redirect()
            ->route('expenses.index', ['project' => $projectId])
            ->with('status', 'Expense deleted.');
    }
}
