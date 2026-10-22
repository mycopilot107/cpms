<?php

namespace App\Policies;

use App\Models\Task;
use App\Models\User;

class TaskPolicy
{
    /** Roles that may plan/assign work. */
    private const PLANNERS = [
        User::ROLE_OWNER,
        User::ROLE_ADMIN,
        User::ROLE_MANAGER,
        User::ROLE_SUPERVISOR,
    ];

    public function viewAny(User $user): bool
    {
        return $user->isActive();
    }

    public function view(User $user, Task $task): bool
    {
        return $this->sameCompany($user, $task);
    }

    public function create(User $user): bool
    {
        return $user->isActive() && in_array($user->role, self::PLANNERS, true);
    }

    /** Mobile task access is limited to assignees and active company planners. */
    public function accessApi(User $user, Task $task): bool
    {
        return $user->isActive()
            && $this->sameCompany($user, $task)
            && ($task->assigned_to === $user->id || $user->canManagePlanning());
    }

    public function update(User $user, Task $task): bool
    {
        if (! $this->sameCompany($user, $task)) {
            return false;
        }

        // Planners edit any task; the assignee may update their own task
        // (e.g. move status/progress).
        return in_array($user->role, self::PLANNERS, true)
            || $task->assigned_to === $user->id;
    }

    public function delete(User $user, Task $task): bool
    {
        return $this->sameCompany($user, $task)
            && in_array($user->role, self::PLANNERS, true);
    }

    private function sameCompany(User $user, Task $task): bool
    {
        return $user->company_id !== null && $user->company_id === $task->company_id;
    }
}
