<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;

class ProjectPolicy
{
    /** Managers, admins and owners create/edit projects. */
    private const MANAGERS = [
        User::ROLE_OWNER,
        User::ROLE_ADMIN,
        User::ROLE_MANAGER,
    ];

    public function viewAny(User $user): bool
    {
        return $user->isActive();
    }

    public function view(User $user, Project $project): bool
    {
        return $this->sameCompany($user, $project);
    }

    public function create(User $user): bool
    {
        return $user->isActive() && in_array($user->role, self::MANAGERS, true);
    }

    public function update(User $user, Project $project): bool
    {
        return $this->sameCompany($user, $project)
            && in_array($user->role, self::MANAGERS, true);
    }

    public function delete(User $user, Project $project): bool
    {
        // Deleting a project (and cascading its tasks) is owner/admin only.
        return $this->sameCompany($user, $project) && $user->isAdmin();
    }

    private function sameCompany(User $user, Project $project): bool
    {
        return $user->company_id !== null && $user->company_id === $project->company_id;
    }
}
