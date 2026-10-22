<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View|RedirectResponse
    {
        $user = $request->user();

        if ($user->isSuperAdmin()) {
            return redirect()->route('platform.companies.index');
        }

        $company = $user->company;

        // Project/Task counts are auto-scoped to the tenant by BelongsToCompany.
        // Users have no global scope, so scope them explicitly by company_id.
        $stats = [
            'projects_total' => Project::count(),
            'projects_active' => Project::where('status', Project::STATUS_ACTIVE)->count(),
            'tasks_total' => Task::count(),
            'tasks_open' => Task::whereIn('status', [Task::STATUS_PENDING, Task::STATUS_IN_PROGRESS])->count(),
            'tasks_overdue' => Task::whereNotIn('status', [Task::STATUS_COMPLETED, Task::STATUS_CANCELLED])
                ->whereNotNull('due_date')
                ->whereDate('due_date', '<', now())
                ->count(),
            'users_total' => User::where('company_id', $user->company_id)->count(),
        ];

        $recentProjects = Project::withCount('tasks')->latest()->take(5)->get();

        $myTasks = Task::with('project')
            ->where('assigned_to', $user->id)
            ->whereIn('status', [Task::STATUS_PENDING, Task::STATUS_IN_PROGRESS])
            ->orderByRaw('due_date is null, due_date')
            ->take(8)
            ->get();

        return view('dashboard', [
            'company' => $company,
            'stats' => $stats,
            'recentProjects' => $recentProjects,
            'myTasks' => $myTasks,
            'trialDaysRemaining' => $company?->trialDaysRemaining() ?? 0,
            'isOnTrial' => (bool) ($company?->isOnTrial() && ! $company?->hasActiveSubscription()),
            'subscription' => $company?->activeSubscription(),
            'usedSeats' => $company?->usedSeats() ?? 0,
        ]);
    }
}
