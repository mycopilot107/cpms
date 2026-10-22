<?php

namespace App\Http\Controllers;

use App\Models\Milestone;
use App\Models\Project;
use App\Models\Task;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PlanningController extends Controller
{
    /**
     * Month calendar overlaying task due-dates and milestone due-dates. All
     * queries are auto-scoped to the tenant by BelongsToCompany.
     */
    public function calendar(Request $request): View
    {
        abort_unless($request->user()->isActive(), 403);

        // Anchor month from ?month=YYYY-MM, falling back to the current month.
        $anchor = $this->parseMonth($request->string('month')->toString());

        $start = $anchor->copy()->startOfMonth();
        $end = $anchor->copy()->endOfMonth();

        $tasks = Task::with('project')
            ->whereNotNull('due_date')
            ->whereBetween('due_date', [$start->toDateString(), $end->toDateString()])
            ->get();

        $milestones = Milestone::with('project')
            ->whereNotNull('due_date')
            ->whereBetween('due_date', [$start->toDateString(), $end->toDateString()])
            ->get();

        // Bucket events by day-of-month for the grid.
        $eventsByDay = [];
        foreach ($tasks as $task) {
            $day = (int) $task->due_date->format('j');
            $eventsByDay[$day][] = [
                'type' => 'task',
                'label' => $task->title,
                'project' => $task->project?->name,
                'status' => $task->status,
                'url' => route('tasks.show', $task),
            ];
        }
        foreach ($milestones as $milestone) {
            $day = (int) $milestone->due_date->format('j');
            $eventsByDay[$day][] = [
                'type' => 'milestone',
                'label' => $milestone->name,
                'project' => $milestone->project?->name,
                'status' => $milestone->status,
                'url' => route('milestones.index', ['project' => $milestone->project_id]),
            ];
        }

        // Leading blank cells so the 1st lands under the right weekday (Mon-first).
        $leadingBlanks = ($start->dayOfWeekIso - 1);

        return view('planning.calendar', [
            'anchor' => $anchor,
            'daysInMonth' => $anchor->daysInMonth,
            'leadingBlanks' => $leadingBlanks,
            'eventsByDay' => $eventsByDay,
            'prevMonth' => $anchor->copy()->subMonth()->format('Y-m'),
            'nextMonth' => $anchor->copy()->addMonth()->format('Y-m'),
            'today' => Carbon::today(),
        ]);
    }

    /**
     * A lightweight Gantt built from CSS bars — one row per task, positioned by
     * start_date/due_date across the visible window. No JS charting library.
     */
    public function gantt(Request $request): View
    {
        abort_unless($request->user()->isActive(), 403);

        $projectId = $request->integer('project');
        $project = $projectId ? Project::find($projectId) : null;

        $tasks = Task::with('project')
            ->when($project, fn ($q) => $q->where('project_id', $project->id))
            ->where(function ($q) {
                $q->whereNotNull('start_date')->orWhereNotNull('due_date');
            })
            ->orderByRaw('start_date is null, start_date')
            ->orderByRaw('due_date is null, due_date')
            ->get();

        // Determine the timeline window from the data (fallback: this month).
        $dates = collect();
        foreach ($tasks as $task) {
            if ($task->start_date) {
                $dates->push($task->start_date);
            }
            if ($task->due_date) {
                $dates->push($task->due_date);
            }
        }

        if ($dates->isEmpty()) {
            $windowStart = Carbon::today()->startOfMonth();
            $windowEnd = Carbon::today()->endOfMonth();
        } else {
            $windowStart = $dates->min()->copy()->startOfDay();
            $windowEnd = $dates->max()->copy()->startOfDay();
        }

        // Guard against a zero-width window (single date).
        if ($windowStart->equalTo($windowEnd)) {
            $windowEnd = $windowEnd->copy()->addDays(1);
        }

        $totalDays = max(1, $windowStart->diffInDays($windowEnd));

        // Build positioned bars (percent offsets/width relative to the window).
        $bars = [];
        foreach ($tasks as $task) {
            $barStart = $task->start_date ?: $task->due_date;
            $barEnd = $task->due_date ?: $task->start_date;

            if ($barEnd->lessThan($barStart)) {
                [$barStart, $barEnd] = [$barEnd, $barStart];
            }

            $offsetDays = $windowStart->diffInDays($barStart);
            $spanDays = max(1, $barStart->diffInDays($barEnd));

            $bars[] = [
                'task' => $task,
                'left' => round(($offsetDays / $totalDays) * 100, 4),
                'width' => round(min($spanDays, $totalDays - $offsetDays) / $totalDays * 100, 4),
            ];
        }

        // Month tick marks across the header.
        $months = [];
        $cursor = $windowStart->copy()->startOfMonth();
        while ($cursor->lessThanOrEqualTo($windowEnd)) {
            $offsetDays = max(0, $windowStart->diffInDays($cursor, false));
            $months[] = [
                'label' => $cursor->format('M Y'),
                'left' => round(($offsetDays / $totalDays) * 100, 4),
            ];
            $cursor->addMonth();
        }

        return view('planning.gantt', [
            'bars' => $bars,
            'projects' => Project::orderBy('name')->get(),
            'selectedProject' => $project,
            'windowStart' => $windowStart,
            'windowEnd' => $windowEnd,
            'months' => $months,
            'today' => Carbon::today(),
            'todayLeft' => Carbon::today()->betweenIncluded($windowStart, $windowEnd)
                ? round(($windowStart->diffInDays(Carbon::today()) / $totalDays) * 100, 4)
                : null,
        ]);
    }

    /** Parse a YYYY-MM string into a Carbon at the 1st, or default to this month. */
    private function parseMonth(string $value): Carbon
    {
        if (preg_match('/^\d{4}-\d{2}$/', $value)) {
            try {
                return Carbon::createFromFormat('Y-m-d', $value.'-01')->startOfDay();
            } catch (\Throwable) {
                // fall through
            }
        }

        return Carbon::today()->startOfMonth();
    }
}
