<?php

namespace App\Services;

use App\Models\DailyReport;
use App\Models\DailyReportPhoto;
use App\Models\Milestone;
use App\Models\ProjectShareLink;
use App\Models\Task;
use App\Models\TaskPhoto;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Everything one week of a project looks like to its client: overall
 * progress, milestones, what was finished, what is running late, what comes
 * next, the daily site reports, and photos. Finance data is never included.
 *
 * Runs without a signed-in user (the client has no login), so every query
 * is pinned to the link's project explicitly instead of relying on the
 * tenant scope.
 */
class WeeklyProjectReport
{
    public const MAX_PHOTOS = 24;

    /**
     * @return array<string, mixed>
     */
    public function build(ProjectShareLink $link): array
    {
        $project = $link->project()->withoutGlobalScopes()->firstOrFail();
        $start = $link->week_start->copy()->startOfDay();
        $end = $link->week_end->copy()->endOfDay();

        $tasks = $this->scoped(Task::query(), $project->id);
        $openTasks = fn () => (clone $tasks)->whereNotIn('status', [Task::STATUS_COMPLETED, Task::STATUS_CANCELLED]);

        // "Overdue" as of the end of that week, or today for the current week.
        $overdueBefore = today()->lessThan($end) ? today() : $end->copy()->addDay()->startOfDay();

        $reports = $this->scoped(DailyReport::query(), $project->id)
            ->whereBetween('report_date', [$start->toDateString(), $end->toDateString()])
            ->orderBy('report_date')
            ->get();

        return [
            'link' => $link,
            'project' => $project,
            'companyName' => $project->company()->value('name'),
            'sharedBy' => $link->creator()->withoutGlobalScopes()->first(),
            'progress' => (int) round((float) (clone $tasks)->where('status', '!=', Task::STATUS_CANCELLED)->avg('progress')),
            'taskCounts' => [
                'total' => (clone $tasks)->where('status', '!=', Task::STATUS_CANCELLED)->count(),
                'completed' => (clone $tasks)->where('status', Task::STATUS_COMPLETED)->count(),
            ],
            'milestones' => $this->scoped(Milestone::query(), $project->id)->orderBy('sort_order')->orderBy('due_date')->get(),
            'completedThisWeek' => (clone $tasks)->where('status', Task::STATUS_COMPLETED)->whereBetween('completed_at', [$start, $end])->orderBy('completed_at')->get(),
            'inProgress' => $openTasks()->where('status', Task::STATUS_IN_PROGRESS)->orderByRaw('due_date is null, due_date')->limit(15)->get(),
            'overdue' => $openTasks()->whereNotNull('due_date')->whereDate('due_date', '<', $overdueBefore->toDateString())->orderBy('due_date')->limit(15)->get(),
            'nextWeek' => $openTasks()->whereBetween('due_date', [$end->copy()->addDay()->toDateString(), $end->copy()->addDays(7)->toDateString()])->orderBy('due_date')->limit(15)->get(),
            'days' => $reports->groupBy(fn (DailyReport $r) => $r->report_date->toDateString()),
            'labour' => $this->labourSummary($reports),
            'photos' => $link->include_photos ? $this->photos($project->id, $start, $end, $reports) : collect(),
        ];
    }

    /**
     * Whether a photo may be shown through this link: same project, from the week.
     */
    public function photoBelongsToLink(ProjectShareLink $link, string $kind, int $id): ?Model
    {
        if (! $link->include_photos) {
            return null;
        }

        $start = $link->week_start->copy()->startOfDay();
        $end = $link->week_end->copy()->endOfDay();

        return match ($kind) {
            'task' => $this->scopedPhotos(TaskPhoto::query())
                ->whereKey($id)
                ->whereHas('task', fn ($q) => $q->withoutGlobalScopes()->where('project_id', $link->project_id))
                ->whereBetween('created_at', [$start, $end])
                ->first(),
            'daily' => $this->scopedPhotos(DailyReportPhoto::query())
                ->whereKey($id)
                ->whereHas('report', fn ($q) => $q->withoutGlobalScopes()
                    ->where('project_id', $link->project_id)
                    ->whereBetween('report_date', [$start->toDateString(), $end->toDateString()]))
                ->first(),
            default => null,
        };
    }

    /**
     * @param  Collection<int, DailyReport>  $reports
     * @return array{labour_days: int, days_reported: int, average: int, by_trade: array<string, int>}
     */
    private function labourSummary(Collection $reports): array
    {
        // Several people may report the same day; count the busiest report per day.
        $perDay = $reports->groupBy(fn (DailyReport $r) => $r->report_date->toDateString())
            ->map(fn (Collection $day) => $day->max('labour_count'));

        $byTrade = [];
        foreach ($reports as $report) {
            foreach ($report->labour_breakdown ?? [] as $row) {
                $byTrade[$row['trade']] = ($byTrade[$row['trade']] ?? 0) + (int) $row['count'];
            }
        }
        arsort($byTrade);

        return [
            'labour_days' => (int) $perDay->sum(),
            'days_reported' => $perDay->count(),
            'average' => $perDay->count() > 0 ? (int) round($perDay->avg()) : 0,
            'by_trade' => $byTrade,
        ];
    }

    /**
     * @param  Collection<int, DailyReport>  $reports
     * @return Collection<int, array{kind: string, id: int, caption: ?string, when: ?Carbon, label: string}>
     */
    private function photos(int $projectId, Carbon $start, Carbon $end, Collection $reports): Collection
    {
        $daily = $this->scopedPhotos(DailyReportPhoto::query())
            ->whereIn('daily_report_id', $reports->pluck('id'))
            ->get()
            ->map(fn (DailyReportPhoto $photo) => [
                'kind' => 'daily',
                'id' => $photo->id,
                'caption' => $photo->caption,
                'when' => $photo->taken_at ?? $photo->created_at,
                'label' => 'Site report',
            ]);

        $taskTitles = $this->scoped(Task::query(), $projectId)->pluck('title', 'id');
        $task = $this->scopedPhotos(TaskPhoto::query())
            ->whereIn('task_id', $taskTitles->keys())
            ->whereBetween('created_at', [$start, $end])
            ->get()
            ->map(fn (TaskPhoto $photo) => [
                'kind' => 'task',
                'id' => $photo->id,
                'caption' => $photo->caption,
                'when' => $photo->taken_at ?? $photo->created_at,
                'label' => (string) ($taskTitles[$photo->task_id] ?? 'Task'),
            ]);

        return $daily->concat($task)
            ->sortByDesc(fn (array $photo) => $photo['when']?->getTimestamp() ?? 0)
            ->take(self::MAX_PHOTOS)
            ->values();
    }

    /**
     * @template T of Model
     *
     * @param  Builder<T>  $query
     * @return Builder<T>
     */
    private function scoped(Builder $query, int $projectId): Builder
    {
        return $query->withoutGlobalScopes()->where('project_id', $projectId);
    }

    /**
     * @template T of Model
     *
     * @param  Builder<T>  $query
     * @return Builder<T>
     */
    private function scopedPhotos(Builder $query): Builder
    {
        return $query->withoutGlobalScopes();
    }
}
