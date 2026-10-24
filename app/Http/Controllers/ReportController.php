<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Project;
use App\Models\ProjectPayment;
use App\Models\Task;
use App\Support\Money;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    /** Report hub. */
    public function index(Request $request): View
    {
        abort_unless($request->user()->canViewReports(), 403);

        return view('reports.index');
    }

    /**
     * Project report: status, schedule, budget vs spend, progress.
     * ?export=csv streams the same dataset as a download.
     */
    public function projects(Request $request): View|StreamedResponse
    {
        abort_unless($request->user()->canViewReports(), 403);

        $projects = Project::query()
            ->withCount('tasks')
            ->withSum('expenses as expense_total', 'amount')
            ->withAvg(['tasks as avg_progress' => fn ($q) => $q->where('status', '!=', Task::STATUS_CANCELLED)], 'progress')
            ->orderBy('name')
            ->get();

        if ($request->query('export') === 'csv') {
            $code = Money::code();

            return $this->streamCsv('project-report.csv',
                ['Project', 'Code', 'Status', 'Start', 'End', "Budget ({$code})", "Expenses ({$code})", "Variance ({$code})", 'Tasks', 'Progress %'],
                $projects->map(function (Project $p) {
                    $variance = $p->budget !== null ? (float) $p->budget - (float) $p->expense_total : null;

                    return [
                        $p->name,
                        $p->project_code,
                        $p->status,
                        optional($p->start_date)->format('Y-m-d'),
                        optional($p->end_date)->format('Y-m-d'),
                        $p->budget,
                        $p->expense_total ?? 0,
                        $variance,
                        $p->tasks_count,
                        (int) round((float) $p->avg_progress),
                    ];
                })
            );
        }

        return view('reports.projects', ['projects' => $projects]);
    }

    /** Task report with optional project + status filters. */
    public function tasks(Request $request): View|StreamedResponse
    {
        abort_unless($request->user()->canViewReports(), 403);

        $projectId = $request->integer('project');
        $project = $projectId ? Project::find($projectId) : null;

        $status = $request->string('status')->toString();
        $status = in_array($status, Task::STATUSES, true) ? $status : null;

        $tasks = Task::with(['project', 'assignee'])
            ->when($project, fn ($q) => $q->where('project_id', $project->id))
            ->when($status, fn ($q) => $q->where('status', $status))
            ->orderBy('project_id')
            ->orderByRaw('due_date is null, due_date')
            ->get();

        if ($request->query('export') === 'csv') {
            return $this->streamCsv('task-report.csv',
                ['Task', 'Project', 'Assignee', 'Priority', 'Status', 'Start', 'Due', 'Progress %', 'Est. hrs', 'Actual hrs'],
                $tasks->map(fn (Task $t) => [
                    $t->title,
                    $t->project?->name,
                    $t->assignee?->name,
                    $t->priority,
                    $t->status,
                    optional($t->start_date)->format('Y-m-d'),
                    optional($t->due_date)->format('Y-m-d'),
                    $t->progress,
                    $t->estimated_hours,
                    $t->actual_hours,
                ])
            );
        }

        return view('reports.tasks', [
            'tasks' => $tasks,
            'projects' => Project::orderBy('name')->get(),
            'statuses' => Task::STATUSES,
            'selectedProject' => $project,
            'selectedStatus' => $status,
        ]);
    }

    /** Cost report: expenses grouped by category, plus payment totals. */
    public function cost(Request $request): View|StreamedResponse
    {
        abort_unless($request->user()->canViewReports(), 403);

        $projectId = $request->integer('project');
        $project = $projectId ? Project::find($projectId) : null;

        $byCategory = Expense::query()
            ->when($project, fn ($q) => $q->where('project_id', $project->id))
            ->selectRaw('category, SUM(amount) as total, COUNT(*) as entries')
            ->groupBy('category')
            ->orderByDesc('total')
            ->get();

        $totalExpenses = (float) $byCategory->sum('total');

        $received = (float) ProjectPayment::query()
            ->when($project, fn ($q) => $q->where('project_id', $project->id))
            ->where('direction', ProjectPayment::DIRECTION_INCOMING)
            ->sum('amount');

        $paid = (float) ProjectPayment::query()
            ->when($project, fn ($q) => $q->where('project_id', $project->id))
            ->where('direction', ProjectPayment::DIRECTION_OUTGOING)
            ->sum('amount');

        $budget = (float) Project::query()
            ->when($project, fn ($q) => $q->where('id', $project->id))
            ->sum('budget');

        if ($request->query('export') === 'csv') {
            $rows = $byCategory->map(fn ($row) => [
                $row->category,
                $row->entries,
                $row->total,
            ]);
            $rows->push(['TOTAL', $byCategory->sum('entries'), $totalExpenses]);

            return $this->streamCsv('cost-report.csv',
                ['Category', 'Entries', 'Amount ('.Money::code().')'],
                $rows
            );
        }

        return view('reports.cost', [
            'byCategory' => $byCategory,
            'totalExpenses' => $totalExpenses,
            'received' => $received,
            'paid' => $paid,
            'budget' => $budget,
            'projects' => Project::orderBy('name')->get(),
            'selectedProject' => $project,
        ]);
    }

    /** Progress report: completion by project and task-status counts. */
    public function progress(Request $request): View|StreamedResponse
    {
        abort_unless($request->user()->canViewReports(), 403);

        $projects = Project::query()
            ->withCount([
                'tasks',
                'tasks as completed_tasks_count' => fn ($q) => $q->where('status', Task::STATUS_COMPLETED),
                'tasks as in_progress_tasks_count' => fn ($q) => $q->where('status', Task::STATUS_IN_PROGRESS),
                'tasks as pending_tasks_count' => fn ($q) => $q->where('status', Task::STATUS_PENDING),
                'tasks as overdue_tasks_count' => fn ($q) => $q
                    ->whereNotIn('status', [Task::STATUS_COMPLETED, Task::STATUS_CANCELLED])
                    ->whereNotNull('due_date')
                    ->whereDate('due_date', '<', now()),
            ])
            ->withAvg(['tasks as avg_progress' => fn ($q) => $q->where('status', '!=', Task::STATUS_CANCELLED)], 'progress')
            ->orderBy('name')
            ->get();

        if ($request->query('export') === 'csv') {
            return $this->streamCsv('progress-report.csv',
                ['Project', 'Status', 'Tasks', 'Completed', 'In progress', 'Pending', 'Overdue', 'Avg progress %'],
                $projects->map(fn (Project $p) => [
                    $p->name,
                    $p->status,
                    $p->tasks_count,
                    $p->completed_tasks_count,
                    $p->in_progress_tasks_count,
                    $p->pending_tasks_count,
                    $p->overdue_tasks_count,
                    (int) round((float) $p->avg_progress),
                ])
            );
        }

        return view('reports.progress', ['projects' => $projects]);
    }

    /**
     * Stream a CSV download. $rows is an iterable of flat arrays matching
     * $headers. Everything is already tenant-scoped by the queries above.
     */
    private function streamCsv(string $filename, array $headers, iterable $rows): StreamedResponse
    {
        return response()->streamDownload(function () use ($headers, $rows) {
            $out = fopen('php://output', 'w');
            fputcsv($out, $headers);
            foreach ($rows as $row) {
                fputcsv($out, array_map(function ($value) {
                    // CSV quoting alone does not stop spreadsheet formulas.
                    if (is_string($value) && preg_match('/^[\x00-\x20]*[=+@-]|^[\t\r\n]/', $value)) {
                        return "'".$value;
                    }

                    return $value ?? '';
                }, (array) $row));
            }
            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }
}
